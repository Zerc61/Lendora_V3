<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PenaltyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePenaltyItemRequest;
use App\Http\Requests\StorePenaltyRequest;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Maintenance;
use App\Models\Penalty;
use App\Models\User;
use App\Services\BapService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PenaltyController extends Controller
{
    public function __construct(
        private readonly BapService $bap,
        private readonly PdfService $pdf,
    ) {}

    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'status' => PenaltyStatus::tryFrom((string) $request->query('status'))?->value,
        ];

        $penalties = Penalty::query()
            ->with(['borrower', 'asset', 'booking'])
            ->where('organization_id', $organizationId)
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('bap_number', 'like', "%{$search}%")
                        ->orWhereHas('borrower', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->latest('incident_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.penalties.index', [
            'penalties' => $penalties,
            'filters' => $filters,
            'statuses' => PenaltyStatus::cases(),
            'summary' => [
                'total' => Penalty::where('organization_id', $organizationId)->count(),
                'waiting' => Penalty::where('organization_id', $organizationId)
                    ->whereIn('status', [PenaltyStatus::WaitingPayment->value, PenaltyStatus::Partial->value, PenaltyStatus::Overdue->value])
                    ->count(),
                'outstanding' => (int) Penalty::where('organization_id', $organizationId)
                    ->whereIn('status', [PenaltyStatus::WaitingPayment->value, PenaltyStatus::Partial->value, PenaltyStatus::Overdue->value])
                    ->sum('total_amount'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('admin.penalties.create', [
            'bookings' => Booking::query()
                ->with(['borrower', 'items.asset'])
                ->where('organization_id', $organizationId)
                ->whereIn('status', ['active', 'returned'])
                ->latest('requested_start_date')
                ->limit(50)
                ->get(),
            'borrowers' => User::query()
                ->where('organization_id', $organizationId)
                ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'))
                ->orderBy('name')
                ->get(),
            'assets' => Asset::query()
                ->where('organization_id', $organizationId)
                ->orderBy('code')
                ->limit(200)
                ->get(),
            'maintenances' => Maintenance::query()
                ->with('asset')
                ->whereHas('asset', fn ($query) => $query->where('organization_id', $organizationId))
                ->latest()
                ->limit(50)
                ->get(),
            'selectedBookingId' => $request->integer('booking_id') ?: null,
        ]);
    }

    public function store(StorePenaltyRequest $request): RedirectResponse
    {
        $data = $request->payload();
        $data['photo_paths'] = $this->storePhotos($request) ?: null;

        $penalty = $this->bap->create($request->user(), $data);

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('messages.bap.created'));
    }

    public function show(Request $request, Penalty $penalty): View
    {
        $this->assertOrganization($request, $penalty);

        return view('admin.penalties.show', [
            'penalty' => $penalty->load([
                'items',
                'payments.borrower',
                'payments.verifier',
                'borrower',
                'asset',
                'booking',
                'maintenance',
                'issuer',
                'creator',
            ]),
        ]);
    }

    public function storeItem(StorePenaltyItemRequest $request, Penalty $penalty): RedirectResponse
    {
        $this->assertOrganization($request, $penalty);

        $this->bap->addItem($penalty, $request->user(), $request->validated());

        return back()->with('status', __('messages.bap.item_added'));
    }

    public function print(Request $request, Penalty $penalty): View|Response
    {
        $this->assertOrganization($request, $penalty);

        $data = [
            'penalty' => $penalty->load(['items', 'borrower', 'asset', 'booking', 'maintenance', 'issuer']),
        ];

        if ($request->query('format') === 'pdf') {
            return $this->pdf->download('admin.penalties.print', $data, $penalty->bap_number.'.pdf');
        }

        return view('admin.penalties.print', $data);
    }

    public function issue(Request $request, Penalty $penalty): RedirectResponse
    {
        $this->assertOrganization($request, $penalty);

        $this->bap->issue($penalty, $request->user());

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('messages.bap.issued'));
    }

    public function escalate(Request $request, Penalty $penalty): RedirectResponse
    {
        $this->assertOrganization($request, $penalty);

        app(\App\Services\PenaltyService::class)->escalate($penalty, $request->user());

        return redirect()
            ->route('admin.penalties.show', $penalty)
            ->with('status', __('messages.bap.escalated'));
    }

    /**
     * @return array<int, string>
     */
    private function storePhotos(Request $request): array
    {
        $paths = [];

        foreach ((array) $request->file('photos', []) as $photo) {
            $paths[] = $photo->store('baps/'.$request->user()->organization_id, 'public');
        }

        return $paths;
    }

    private function assertOrganization(Request $request, Penalty $penalty): void
    {
        abort_unless($penalty->organization_id === $request->user()->organization_id, 404);
    }
}