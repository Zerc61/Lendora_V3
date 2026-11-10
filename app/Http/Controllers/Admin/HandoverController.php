<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHandoverRequest;
use App\Models\Booking;
use App\Services\HandoverMediaService;
use App\Services\HandoverService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class HandoverController extends Controller
{
    public function __construct(
        private readonly HandoverService $handovers,
        private readonly HandoverMediaService $media,
        private readonly PdfService $pdf,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'type' => in_array($request->query('type'), ['pickup', 'return'], true) ? (string) $request->query('type') : null,
        ];

        $statuses = match ($filters['type']) {
            'pickup' => [BookingStatus::Approved->value],
            'return' => [BookingStatus::Active->value],
            default => [BookingStatus::Approved->value, BookingStatus::Active->value],
        };

        $bookings = Booking::query()
            ->with(['borrower', 'items.asset.photos'])
            ->where('organization_id', $request->user()->organization_id)
            ->whereIn('status', $statuses)
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhereHas('borrower', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('requested_start_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.handovers.index', compact('bookings', 'filters'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $type = HandoverType::tryFrom((string) $request->query('type'))
            ?? ($booking->status->value === 'active' ? HandoverType::Return : HandoverType::Pickup);

        $items = $booking->items()->with('asset.photos')->get();

        return view('admin.bookings.handover', [
            'booking' => $booking->load('borrower'),
            'items' => $items,
            'type' => $type,
        ]);
    }

    public function store(StoreHandoverRequest $request, Booking $booking): RedirectResponse
    {
        $type = HandoverType::from((string) $request->input('type'));
        $conditions = (array) $request->input('conditions', []);
        $notes = $request->input('notes');
        $options = $this->media->options($request, $booking);

        if ($type === HandoverType::Pickup) {
            $this->handovers->recordPickup($booking, $request->user(), $conditions, $notes, $options);
            $message = __('messages.handover.pickup_recorded');
        } else {
            $this->handovers->recordReturn($booking, $request->user(), $conditions, $notes, $options);
            $message = __('messages.handover.return_recorded');
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', $message);
    }

    public function receipt(Request $request, Booking $booking, string $type): View|Response
    {
        $this->authorize('view', $booking);

        $handoverType = HandoverType::from($type);

        $handover = $booking->handovers()
            ->with(['items.bookingItem.asset', 'performedBy'])
            ->where('type', $handoverType->value)
            ->latest('happened_at')
            ->firstOrFail();

        $data = [
            'booking' => $booking,
            'handover' => $handover,
            'type' => $handoverType,
        ];

        if ($request->query('format') === 'pdf') {
            return $this->pdf->download('admin.bookings.receipt', $data, $booking->code.'-'.$type.'.pdf');
        }

        return view('admin.bookings.receipt', $data);
    }
}