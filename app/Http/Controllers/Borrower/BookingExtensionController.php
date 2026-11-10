<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingExtensionController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('requestExtension', $booking);

        $currentEnd = $booking->due_at ?? $booking->requested_end_date;

        $request->validate([
            'new_end_date' => ['required', 'date', 'after:'.$currentEnd->format('Y-m-d')],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->bookings->requestExtension(
            $booking,
            $request->user(),
            \Carbon\Carbon::parse($request->input('new_end_date')),
            $request->input('reason'),
        );

        return redirect()
            ->route('borrower.bookings.show', $booking)
            ->with('status', __('messages.booking.extension_requested'));
    }

    public function cancel(Request $request, Booking $booking, BookingExtension $extension): RedirectResponse
    {
        $this->authorize('cancel', $extension);

        $extension->update(['status' => 'cancelled']);

        return redirect()
            ->route('borrower.bookings.show', $booking)
            ->with('status', __('messages.booking.extension_cancelled'));
    }

    public function form(Request $request, Booking $booking): View
    {
        $this->authorize('requestExtension', $booking);

        return view('borrower.bookings.extension-form', compact('booking'));
    }
}