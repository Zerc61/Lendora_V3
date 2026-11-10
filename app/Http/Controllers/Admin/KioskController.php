<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Http\Controllers\Controller;
use App\Http\Requests\KioskProcessRequest;
use App\Models\Booking;
use App\Models\QueueTicket;
use App\Services\HandoverMediaService;
use App\Services\KioskService;
use App\Services\QueueService;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function __construct(
        private readonly KioskService $kiosk,
        private readonly QueueService $queue,
        private readonly ShiftService $shifts,
        private readonly HandoverMediaService $media,
    ) {}

    public function index(Request $request): View
    {
        $lookup = null;
        $bookingId = $request->session()->get('kiosk.booking_id');

        if ($bookingId !== null) {
            $booking = Booking::query()
                ->with(['borrower', 'items.asset'])
                ->where('organization_id', $request->user()->organization_id)
                ->find($bookingId);

            if ($booking !== null) {
                $type = HandoverType::tryFrom((string) $request->session()->get('kiosk.type'))
                    ?? ($booking->status === BookingStatus::Active ? HandoverType::Return : HandoverType::Pickup);

                $lookup = [
                    'booking' => $booking,
                    'type' => $type,
                    'preflight' => $this->kiosk->preflight($booking, $type),
                ];
            }
        }

        return view('admin.kiosk.index', [
            'lookup' => $lookup,
            'shift' => $this->shifts->current($request->user()->organization_id),
            'queue' => $this->queue->board($request->user()->organization_id),
        ]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:100'],
        ]);

        $result = $this->kiosk->lookup($validated['query'], $request->user());

        return redirect()
            ->route('admin.kiosk.index')
            ->with([
                'kiosk.booking_id' => $result['booking']->id,
                'kiosk.type' => $result['type']->value,
            ]);
    }

    public function process(KioskProcessRequest $request): RedirectResponse
    {
        $booking = $request->booking();
        $type = HandoverType::from((string) $request->input('type'));

        try {
            $processed = $this->kiosk->process(
                $booking,
                $request->user(),
                $type,
                (array) $request->input('conditions', []),
                $this->media->options($request, $booking) + [
                    'notes' => $request->input('notes'),
                    'inspect' => (bool) $request->boolean('inspect', true),
                    'inspection_notes' => $request->input('inspection_notes'),
                ],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('admin.kiosk.index')
            ->with('status', $type === HandoverType::Pickup
                ? __('messages.handover.pickup_recorded')
                : __('messages.handover.return_recorded'))
            ->with('kiosk.receipt', [
                'booking_id' => $processed->id,
                'type' => $type->value,
            ]);
    }

    public function queue(Request $request): View
    {
        return view('admin.kiosk.queue', [
            'board' => $this->queue->board($request->user()->organization_id),
            'shift' => $this->shifts->current($request->user()->organization_id),
            'notes' => $this->shifts->notes($request->user()->organization_id, 5),
        ]);
    }

    public function callNext(Request $request): RedirectResponse
    {
        $counter = $request->string('counter')->toString() ?: null;
        $ticket = $this->queue->callNext($request->user()->organization_id, $counter, $request->user());

        return back()->with('status', $ticket === null
            ? __('messages.queue.empty')
            : __('messages.queue.called', ['number' => $ticket->number]));
    }

    public function serve(Request $request, QueueTicket $ticket): RedirectResponse
    {
        $this->assertOrganization($request, $ticket);

        $this->queue->serve($ticket, $request->user());

        return back()->with('status', __('messages.queue.served', ['number' => $ticket->number]));
    }

    private function assertOrganization(Request $request, QueueTicket $ticket): void
    {
        abort_unless($ticket->organization_id === $request->user()->organization_id, 404);
    }
}
