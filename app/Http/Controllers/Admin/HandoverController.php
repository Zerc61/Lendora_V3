<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HandoverType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHandoverRequest;
use App\Models\Booking;
use App\Services\HandoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HandoverController extends Controller
{
    public function __construct(private readonly HandoverService $handovers) {}

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $type = HandoverType::tryFrom((string) $request->query('type'))
            ?? ($booking->status->value === 'active' ? HandoverType::Return : HandoverType::Pickup);

        $items = $booking->items()->with('asset')->get();

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

        if ($type === HandoverType::Pickup) {
            $this->handovers->recordPickup($booking, $request->user(), $conditions, $notes);
            $message = __('messages.handover.pickup_recorded');
        } else {
            $this->handovers->recordReturn($booking, $request->user(), $conditions, $notes);
            $message = __('messages.handover.return_recorded');
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', $message);
    }
}
