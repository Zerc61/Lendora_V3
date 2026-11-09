<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\InspectionResult;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $categoryId = $request->integer('category_id') ?: null;
        $organizationId = $request->user()->organization_id;

        $bookingQuery = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('requested_start_date', [$from->toDateString(), $to->toDateString()])
            ->when($categoryId, fn (Builder $query, int $id) => $query->whereHas(
                'items.asset',
                fn (Builder $query) => $query->where('category_id', $id),
            ));

        $loans = (clone $bookingQuery)
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->with('items.asset')
            ->get();

        $assetQuery = Asset::query()->where('organization_id', $organizationId)
            ->when($categoryId, fn (Builder $query, int $id) => $query->where('category_id', $id));

        $assetsTotal = (clone $assetQuery)->count();
        $periodDays = max(1, $from->diffInDays($to) + 1);
        $borrowedAssetDays = $loans->sum(fn (Booking $booking) => $booking->items->count() * $booking->durationInDays());

        $kpis = [
            'total_loans' => $loans->count(),
            'borrowed_days' => (int) $loans->sum(fn (Booking $booking) => $booking->durationInDays()),
            'utilization' => $assetsTotal > 0
                ? round($borrowedAssetDays / ($assetsTotal * $periodDays) * 100, 1)
                : 0,
            'damages' => $this->damagesQuery($organizationId, $from, $to, $categoryId)->count(),
            'late_returns' => (clone $bookingQuery)
                ->whereIn('status', [
                    BookingStatus::Approved->value,
                    BookingStatus::Active->value,
                    BookingStatus::Returned->value,
                ])
                ->whereNotNull('returned_at')
                ->whereNotNull('due_at')
                ->whereColumn('returned_at', '>', 'due_at')
                ->count(),
        ];

        $utilization = $this->utilizationRows($organizationId, $from, $to, $categoryId);
        $topBorrowers = $this->topBorrowers($bookingQuery);
        $monthlyTrend = $this->monthlyTrend($loans);
        $topAssets = $this->topAssets($organizationId, $from, $to, $categoryId);
        $damages = $this->damagesQuery($organizationId, $from, $to, $categoryId)
            ->with(['asset', 'inspector'])
            ->latest('checked_at')
            ->limit(10)
            ->get();

        $filters = [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'category_id' => $categoryId,
        ];

        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.index', compact(
            'kpis',
            'utilization',
            'topBorrowers',
            'monthlyTrend',
            'topAssets',
            'damages',
            'filters',
            'categories',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $categoryId = $request->integer('category_id') ?: null;
        $organizationId = $request->user()->organization_id;

        $bookings = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('requested_start_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->when($categoryId, fn (Builder $query, int $id) => $query->whereHas(
                'items.asset',
                fn (Builder $query) => $query->where('category_id', $id),
            ))
            ->with(['borrower', 'items.asset'])
            ->orderBy('requested_start_date')
            ->get();

        $filename = "laporan-peminjaman-{$from->format('Ymd')}-{$to->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($bookings): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Kode', 'Peminjam', 'Mulai', 'Selesai', 'Durasi (hari)', 'Status', 'Unit']);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->code,
                    $booking->borrower?->name,
                    $booking->requested_start_date->toDateString(),
                    $booking->requested_end_date->toDateString(),
                    $booking->durationInDays(),
                    $booking->status->value,
                    $booking->items->map(fn (BookingItem $item) => $item->asset?->code)->filter()->implode('; '),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        $from = $this->parseDate($request->query('date_from')) ?? now()->startOfMonth();
        $to = $this->parseDate($request->query('date_to')) ?? now()->endOfMonth();

        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function damagesQuery(int $organizationId, Carbon $from, Carbon $to, ?int $categoryId): Builder
    {
        return Inspection::query()
            ->where('result', '!=', InspectionResult::Ok->value)
            ->whereBetween('checked_at', [$from, $to])
            ->whereHas('asset', function (Builder $query) use ($organizationId, $categoryId): void {
                $query->where('organization_id', $organizationId)
                    ->when($categoryId, fn (Builder $query, int $id) => $query->where('category_id', $id));
            });
    }

    /**
     * @return Collection<int, array{name: string, assets: int, borrowed_days: int, utilization: float}>
     */
    private function utilizationRows(int $organizationId, Carbon $from, Carbon $to, ?int $categoryId): Collection
    {
        $periodDays = max(1, $from->diffInDays($to) + 1);

        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->when($categoryId, fn (Builder $query, int $id) => $query->whereKey($id))
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        $bookings = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('requested_start_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->with('items.asset')
            ->get();

        return $categories->map(function (Category $category) use ($bookings, $periodDays): array {
            $borrowedDays = $bookings->sum(function (Booking $booking) use ($category): int {
                return $booking->items
                    ->filter(fn (BookingItem $item) => $item->asset?->category_id === $category->id)
                    ->count() * $booking->durationInDays();
            });

            return [
                'name' => $category->name,
                'assets' => $category->assets_count,
                'borrowed_days' => (int) $borrowedDays,
                'utilization' => $category->assets_count > 0
                    ? round($borrowedDays / ($category->assets_count * $periodDays) * 100, 1)
                    : 0,
            ];
        });
    }

    /**
     * @param  Collection<int, Booking>  $loans
     * @return Collection<int, array{month: string, total: int, days: int}>
     */
    private function monthlyTrend(Collection $loans): Collection
    {
        return $loans
            ->groupBy(fn (Booking $booking) => $booking->requested_start_date->format('Y-m'))
            ->sortKeys()
            ->map(fn (Collection $group, string $month) => [
                'month' => $month,
                'total' => $group->count(),
                'days' => (int) $group->sum(fn (Booking $booking) => $booking->durationInDays()),
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{name: ?string, code: ?string, total: int}>
     */
    private function topAssets(int $organizationId, Carbon $from, Carbon $to, ?int $categoryId): Collection
    {
        return BookingItem::query()
            ->whereHas('booking', fn (Builder $query) => $query
                ->where('organization_id', $organizationId)
                ->whereBetween('requested_start_date', [$from->toDateString(), $to->toDateString()])
                ->whereIn('status', [
                    BookingStatus::Approved->value,
                    BookingStatus::Active->value,
                    BookingStatus::Returned->value,
                ]))
            ->when($categoryId, fn (Builder $query, int $id) => $query
                ->whereHas('asset', fn (Builder $query) => $query->where('category_id', $id)))
            ->selectRaw('asset_id, count(*) as total')
            ->groupBy('asset_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('asset')
            ->get()
            ->map(fn (BookingItem $item) => [
                'name' => $item->asset?->name,
                'code' => $item->asset?->code,
                'total' => (int) $item->total,
            ]);
    }

    private function topBorrowers(Builder $bookingQuery): Collection
    {
        return (clone $bookingQuery)
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->selectRaw('borrower_id, count(*) as total')
            ->groupBy('borrower_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('borrower')
            ->get()
            ->map(fn (Booking $booking) => [
                'name' => $booking->borrower?->name,
                'total' => (int) $booking->total,
            ]);
    }
}
