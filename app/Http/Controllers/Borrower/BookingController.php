<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Course;
use App\Services\BookingService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = BookingStatus::tryFrom((string) $request->query('status'))?->value;

        $counts = Booking::query()
            ->where('borrower_id', $user->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $bookings = Booking::query()
            ->with(['items.asset.photos'])
            ->where('borrower_id', $user->id)
            ->when($tab, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $activeBooking = $bookings->firstWhere('status', BookingStatus::Active->value);

        $quota = app(\App\Services\QuotaService::class)->summary($user);
        $compliance = app(\App\Services\BorrowerScoreService::class)->summary($user);
        $sanctions = app(\App\Services\PenaltyService::class)->summaryFor($user);

        return view('borrower.bookings.index', compact('bookings', 'tab', 'counts', 'activeBooking', 'quota', 'compliance', 'sanctions'));
    }

    public function create(Request $request): View
    {
        $ids = collect((array) $request->query('assets', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $assets = Asset::query()
            ->with(['category', 'photos'])
            ->where('organization_id', $request->user()->organization_id)
            ->whereIn('id', $ids)
            ->get();

        return view('borrower.bookings.create', [
            'assets' => $assets,
            'maxLoanDays' => app(SettingService::class)->maxLoanDays(),
            'conflict' => null,
            'courses' => Course::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookings->createRequest(
            $request->user(),
            $request->input('assets'),
            Carbon::parse($request->input('requested_start_date')),
            Carbon::parse($request->input('requested_end_date')),
            (string) $request->input('purpose'),
            $request->filled('course_id') ? (int) $request->input('course_id') : null,
        );

        return redirect()
            ->route('borrower.bookings.show', $booking)
            ->with('status', __('messages.booking.created'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['items.asset.photos', 'borrower', 'approvedBy', 'extensions']);

        return view('borrower.bookings.show', [
            'booking' => $booking,
            'timeline' => $this->bookings->timelineFor($booking),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $this->bookings->cancel($booking, $request->user());

        return redirect()
            ->route('borrower.bookings.show', $booking)
            ->with('status', __('messages.booking.cancelled'));
    }

    public function document(Request $request, Booking $booking, string $type): View
    {
        $this->authorize('view', $booking);

        return view('borrower.bookings.document', [
            'booking' => $booking,
            'type' => $type,
        ]);
    }

    public function returnPass(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['items.asset.photos']);

        return view('borrower.bookings.return-pass', compact('booking'));
    }
}
