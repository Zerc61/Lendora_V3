<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InspectionResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInspectionRequest;
use App\Models\Asset;
use App\Models\BookingItem;
use App\Models\Inspection;
use App\Services\InspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InspectionController extends Controller
{
    public function __construct(private readonly InspectionService $inspections) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'result' => InspectionResult::tryFrom((string) $request->query('result'))?->value,
        ];

        $inspections = Inspection::query()
            ->with(['asset', 'inspector', 'bookingItem.booking'])
            ->whereHas('asset', fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ->when($filters['search'], function ($query, string $search): void {
                $query->whereHas('asset', function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['result'], fn ($query, string $result) => $query->where('result', $result))
            ->latest('checked_at')
            ->paginate(15)
            ->withQueryString();

        $summaryBase = Inspection::query()
            ->whereHas('asset', fn ($query) => $query->where('organization_id', $request->user()->organization_id));

        $counts = (clone $summaryBase)
            ->selectRaw('result, count(*) as aggregate')
            ->groupBy('result')
            ->pluck('aggregate', 'result');

        $summary = [
            'total' => (int) $counts->sum(),
            'ok' => (int) ($counts[InspectionResult::Ok->value] ?? 0),
            'needs_repair' => (int) ($counts[InspectionResult::NeedsRepair->value] ?? 0),
            'damaged' => (int) ($counts[InspectionResult::Damaged->value] ?? 0),
            'today' => (clone $summaryBase)->whereDate('checked_at', now()->toDateString())->count(),
        ];

        return view('admin.inspections.index', compact('inspections', 'filters', 'summary'));
    }

    public function create(Request $request): View
    {
        $assets = Asset::query()
            ->with('category')
            ->where('organization_id', $request->user()->organization_id)
            ->orderBy('code')
            ->get();

        $bookingItem = null;

        if ($request->filled('booking_item')) {
            $bookingItem = BookingItem::query()
                ->with('asset', 'booking')
                ->whereHas('booking', fn ($query) => $query->where('organization_id', $request->user()->organization_id))
                ->find($request->integer('booking_item'));
        }

        return view('admin.inspections.create', compact('assets', 'bookingItem'));
    }

    public function store(StoreInspectionRequest $request): RedirectResponse
    {
        $asset = Asset::query()
            ->where('organization_id', $request->user()->organization_id)
            ->findOrFail($request->integer('asset_id'));

        $bookingItem = $request->filled('booking_item_id')
            ? BookingItem::findOrFail($request->integer('booking_item_id'))
            : null;

        $this->inspections->record(
            $asset,
            $request->user(),
            $bookingItem,
            (string) $request->input('result'),
            $request->input('notes'),
            [
                'context' => $request->input('context'),
                'checklist' => $request->input('checklist'),
                'condition_score' => $request->input('condition_score'),
                'photo_paths' => $this->storePhotos($request, $asset),
            ],
        );

        return redirect()
            ->route('admin.inspections.index')
            ->with('status', __('messages.inspection.recorded'));
    }

    public function show(Request $request, Inspection $inspection): View
    {
        abort_unless(
            $inspection->asset->organization_id === $request->user()->organization_id,
            404,
        );

        return view('admin.inspections.show', [
            'inspection' => $inspection->load(['asset.photos', 'inspector', 'bookingItem.booking', 'maintenance']),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function storePhotos(Request $request, Asset $asset): array
    {
        $paths = [];

        foreach ((array) $request->file('photos', []) as $photo) {
            $paths[] = $photo->store("inspections/{$asset->code}", 'public');
        }

        return $paths;
    }
}
