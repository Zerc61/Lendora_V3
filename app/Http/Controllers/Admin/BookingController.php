<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveBookingRequest;
use App\Http\Requests\ReassignBookingItemRequest;
use App\Http\Requests\RejectBookingRequest;
use App\Http\Requests\StoreWalkInBookingRequest;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\User;
use App\Services\ApprovalQueueService;
use App\Services\BookingService;
use App\Services\GanttService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function approval(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'urgency' => $request->query('urgency') ?: null,
            'sort' => $request->query('sort') ?: 'nearest',
        ];

        return view(
            'admin.bookings.approval',
            app(ApprovalQueueService::class)->for($request->user(), $filters),
        );
    }

    public function walkInForm(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('admin.bookings.walk-in', [
            'borrowers' => User::query()
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'))
                ->orderBy('name')
                ->get(['id', 'name', 'nim']),
            'assets' => Asset::query()
                ->with(['category', 'photos'])
                ->where('organization_id', $organizationId)
                ->where('status', 'available')
                ->orderBy('code')
                ->limit(200)
                ->get(),
        ]);
    }

    public function walkInStore(StoreWalkInBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookings->createWalkIn(
            $request->user(),
            $request->integer('borrower_id'),
            (array) $request->input('asset_ids', []),
            Carbon::parse((string) $request->input('requested_start_date')),
            Carbon::parse((string) $request->input('requested_end_date')),
            (string) $request->input('purpose'),
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.walk_in_created', ['code' => $booking->code]));
    }

    public function suggest(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $alternatives = $this->bookings->suggestAlternative($booking, $request->user());

        return back()->with('status', __('messages.booking.suggested', [
            'count' => count($alternatives),
        ]));
    }

    public function reassign(ReassignBookingItemRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('view', $booking);

        $item = BookingItem::query()->whereKey($request->integer('booking_item_id'))->firstOrFail();
        $replacement = Asset::query()->whereKey($request->integer('asset_id'))->firstOrFail();

        $this->bookings->reassignItem($booking, $item, $replacement, $request->user());

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.reassigned'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['items.asset.photos', 'borrower', 'approvedBy', 'handovers.performedBy', 'extensions']);

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

    public function approveExtension(Request $request, Booking $booking, BookingExtension $extension): RedirectResponse
    {
        abort_unless($extension->booking_id === $booking->id, 404);

        $this->bookings->approveExtension($extension, $request->user());

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.extension_approved'));
    }

    public function rejectExtension(Request $request, Booking $booking, BookingExtension $extension): RedirectResponse
    {
        abort_unless($extension->booking_id === $booking->id, 404);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $this->bookings->rejectExtension(
            $extension,
            $request->user(),
            (string) $request->input('rejection_reason'),
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', __('messages.booking.extension_rejected'));
    }

    public function calendar(Request $request): View
    {
        $organizationId = $request->user()->organization_id;
        $categoryId = $request->integer('category_id') ?: null;
        $requestedDays = $request->integer('days');

        if ($requestedDays > 0) {
            try {
                $start = Carbon::parse((string) $request->query('date_from', now()->toDateString()))->startOfDay();
            } catch (\Throwable) {
                $start = now()->startOfDay();
            }

            $days = max(7, min(60, $requestedDays));
        } else {
            try {
                $start = Carbon::createFromFormat('Y-m', (string) $request->query('month', now()->format('Y-m')))->startOfMonth();
            } catch (\Throwable) {
                $start = now()->startOfMonth();
            }

            $days = $start->daysInMonth;
        }

        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $gantt = app(GanttService::class)->build($organizationId, $start, $days, $categoryId);

        $filters = [
            'month' => $start->format('Y-m'),
            'date_from' => $start->toDateString(),
            'days' => $days,
            'category_id' => $categoryId,
        ];

        return view('admin.bookings.calendar', compact('gantt', 'filters', 'categories'));
    }

    public function calendarExport(Request $request): StreamedResponse
    {
        $organizationId = $request->user()->organization_id;
        $start = Carbon::parse((string) $request->query('date_from', now()->toDateString()));
        $days = max(1, min(60, $request->integer('days') ?: 14));
        $categoryId = $request->integer('category_id') ?: null;
        $format = $request->query('format') === 'ics' ? 'ics' : 'csv';

        $gantt = app(GanttService::class)->build($organizationId, $start, $days, $categoryId);
        $filename = "jadwal-ketersediaan-{$start->format('Ymd')}.{$format}";

        if ($format === 'ics') {
            return response()->streamDownload(function () use ($gantt): void {
                echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Lendora//Jadwal//ID\r\n";

                foreach ($gantt['rows'] as $row) {
                    foreach ($row['bars'] as $bar) {
                        if ($bar['type'] !== 'booking') {
                            continue;
                        }

                        $startDate = Carbon::parse($bar['start'])->format('Ymd');
                        $endDate = Carbon::parse($bar['end'])->addDay()->format('Ymd');

                        echo "BEGIN:VEVENT\r\n";
                        echo "UID:{$row['asset']->code}-{$bar['booking_id']}@lendora\r\n";
                        echo "DTSTART;VALUE=DATE:{$startDate}\r\n";
                        echo "DTEND;VALUE=DATE:{$endDate}\r\n";
                        echo "SUMMARY:{$row['asset']->code} {$bar['code']}\r\n";
                        echo "STATUS:{$bar['status']}\r\n";
                        echo "END:VEVENT\r\n";
                    }
                }

                echo "END:VCALENDAR\r\n";
            }, $filename, ['Content-Type' => 'text/calendar; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($gantt): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Kode Aset', 'Nama', 'Kategori', 'Status', 'Mulai', 'Selesai', 'Referensi', 'Konflik']);

            foreach ($gantt['rows'] as $row) {
                foreach ($row['bars'] as $bar) {
                    fputcsv($handle, [
                        $row['asset']->code,
                        $row['asset']->name,
                        $row['asset']->category?->name,
                        $bar['status'],
                        $bar['start'],
                        $bar['end'],
                        $bar['code'],
                        $bar['conflict'] ? 'ya' : 'tidak',
                    ]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
