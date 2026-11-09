<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveBookingRequest;
use App\Http\Requests\RejectBookingRequest;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();
        $organizationId = $user->organization_id;

        $status = $request->has('status') ? $request->query('status') : BookingStatus::Pending->value;

        $filters = [
            'status' => $status ? BookingStatus::tryFrom((string) $status)?->value : null,
            'search' => trim((string) $request->query('search')) ?: null,
            'date_from' => $request->query('date_from') ?: null,
            'date_to' => $request->query('date_to') ?: null,
        ];

        $bookings = Booking::query()
            ->with(['items.asset.photos', 'borrower'])
            ->where('organization_id', $organizationId)
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhereHas('borrower', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['date_from'], fn ($query, string $date) => $query->whereDate('requested_start_date', '>=', $date))
            ->when($filters['date_to'], fn ($query, string $date) => $query->whereDate('requested_end_date', '<=', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Booking::query()
            ->where('organization_id', $organizationId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $canDecide = [];
        $limitExceeded = [];
        $conflicts = [];

        foreach ($bookings as $booking) {
            $reason = $this->bookings->decisionBlockReason($user, $booking);
            $canDecide[$booking->id] = $reason === null;

            if ($reason !== null) {
                $limitExceeded[$booking->id] = $reason;
            }

            if ($booking->status === BookingStatus::Pending) {
                $conflict = $this->bookings->conflictMessageFor(
                    $booking->items->map->asset,
                    $booking->requested_start_date,
                    $booking->requested_end_date,
                );

                if ($conflict !== null) {
                    $conflicts[$booking->id] = $conflict;
                }
            }
        }

        $queueStats = [
            'pending' => (int) ($counts[BookingStatus::Pending->value] ?? 0),
            'approved' => (int) ($counts[BookingStatus::Approved->value] ?? 0),
            'active' => (int) ($counts[BookingStatus::Active->value] ?? 0),
            'returned' => (int) ($counts[BookingStatus::Returned->value] ?? 0),
        ];

        return view('admin.bookings.index', compact('bookings', 'filters', 'counts', 'canDecide', 'limitExceeded', 'conflicts', 'queueStats'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['items.asset.photos', 'borrower', 'approvedBy', 'handovers.performedBy']);

        return view('admin.bookings.show', [
            'booking' => $booking,
            'timeline' => $this->bookings->timelineFor($booking),
            'canDecide' => $this->bookings->decisionBlockReason($request->user(), $booking) === null,
            'blockReason' => $this->bookings->decisionBlockReason($request->user(), $booking),
        ]);
    }

    public function approve(ApproveBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookings->approve($booking, $request->user());

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.approved'));
    }

    public function reject(RejectBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookings->reject($booking, $request->user(), (string) $request->input('rejection_reason'));

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.rejected'));
    }

    public function calendar(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        try {
            $month = Carbon::createFromFormat('Y-m', (string) $request->query('month', now()->format('Y-m')))->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $categoryId = $request->integer('category_id') ?: null;
        $monthEnd = $month->copy()->endOfMonth();

        $units = Asset::query()
            ->with('category')
            ->where('organization_id', $organizationId)
            ->when($categoryId, fn ($query, int $id) => $query->where('category_id', $id))
            ->orderBy('code')
            ->get();

        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $bookings = Booking::query()
            ->with('items:id,booking_id,asset_id')
            ->where('organization_id', $organizationId)
            ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Active->value])
            ->where('requested_start_date', '<=', $monthEnd->toDateString())
            ->where('requested_end_date', '>=', $month->toDateString())
            ->get();

        $ranges = [];

        foreach ($bookings as $booking) {
            foreach ($booking->items as $item) {
                $ranges[$item->asset_id][] = [
                    'start' => $booking->requested_start_date->toDateString(),
                    'end' => $booking->requested_end_date->toDateString(),
                    'code' => $booking->code,
                    'status' => $booking->status->value,
                ];
            }
        }

        $filters = [
            'month' => $month->format('Y-m'),
            'category_id' => $categoryId,
        ];

        return view('admin.bookings.calendar', compact('month', 'units', 'ranges', 'filters', 'categories'));
    }
}
