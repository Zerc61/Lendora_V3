<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SemesterReportService
{
    /**
     * Jam operasional asumsi per unit per hari.
     */
    private const OPERATING_HOURS_PER_DAY = 12;

    public function __construct(private readonly SettingService $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function for(User $prodi): array
    {
        $organizationId = $prodi->organization_id;
        $from = Carbon::parse((string) $this->settings->get('semester_start', '2026-08-01', $organizationId))->startOfDay();
        $to = Carbon::parse((string) $this->settings->get('semester_end', '2026-12-31', $organizationId))->endOfDay();
        $periodDays = max(1, (int) $from->diffInDays($to) + 1);

        $bookings = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereDate('requested_start_date', '<=', $to->toDateString())
            ->whereDate('requested_end_date', '>=', $from->toDateString())
            ->with(['items.asset'])
            ->get();

        $loans = $bookings->whereIn('status', [
            BookingStatus::Approved,
            BookingStatus::Active,
            BookingStatus::Returned,
        ]);

        $rejected = $bookings->where('status', BookingStatus::Rejected);

        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->withCount(['assets' => fn ($query) => $query->where('status', '!=', 'retired')])
            ->orderBy('name')
            ->get();

        $rows = $categories
            ->map(fn (Category $category): array => $this->row(
                $category,
                $loans,
                $rejected,
                $bookings,
                $periodDays,
                $from,
                $to,
            ))
            ->filter(fn (array $row): bool => $row['units'] > 0 || $row['requests'] > 0)
            ->sortByDesc(fn (array $row): float => $row['rejection_rate'])
            ->values();

        $prices = Asset::query()
            ->where('organization_id', $organizationId)
            ->whereIn('category_id', $rows->pluck('category.id'))
            ->whereNotNull('purchase_price')
            ->where('purchase_price', '>', 0)
            ->selectRaw('category_id, avg(purchase_price) as avg_price')
            ->groupBy('category_id')
            ->pluck('avg_price', 'category_id');

        $procurement = $rows
            ->filter(fn (array $row): bool => $row['rejected'] > 0 && $row['requests'] > $row['rejected'])
            ->map(function (array $row) use ($prices): array {
                $accepted = $row['requests'] - $row['rejected'];
                $extraUnits = max(1, (int) ceil($row['units'] * $row['rejected'] / $accepted));
                $average = $prices[$row['category']->id] ?? null;
                $unitPrice = $average !== null ? (int) round($average / 100_000) * 100_000 : null;

                return [
                    ...$row,
                    'extra_units' => $extraUnits,
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice !== null ? $unitPrice * $extraUnits : 0,
                    'priority' => $row['status'] === 'kritis' ? 'urgent' : 'peningkatan',
                ];
            })
            ->values();

        $maintenances = Maintenance::query()
            ->whereHas('asset', fn ($query) => $query->where('organization_id', $organizationId))
            ->whereBetween('created_at', [$from, $to])
            ->with('parts')
            ->get();

        $totalDemand = (float) $rows->sum('demand_hours');
        $totalCapacity = (float) $rows->sum('capacity_hours');
        $totalRequests = (int) $rows->sum('requests');
        $totalRejected = (int) $rows->sum('rejected');

        return [
            'rows' => $rows,
            'procurement' => $procurement,
            'rab_total' => (int) $procurement->sum('subtotal'),
            'kpis' => [
                'demand_hours' => (int) round($totalDemand),
                'capacity_hours' => (int) round($totalCapacity),
                'utilization' => $totalCapacity > 0 ? round($totalDemand / $totalCapacity * 100, 1) : 0,
                'rejected' => $totalRejected,
                'rejection_rate' => $totalRequests > 0 ? round($totalRejected / $totalRequests * 100, 1) : 0,
                'incidents' => $maintenances->count(),
                'maintenance_cost' => (int) $maintenances->sum(
                    fn (Maintenance $maintenance): int => (int) $maintenance->cost + $maintenance->totalCost(),
                ),
                'bottlenecks' => $rows->where('status', 'kritis')->count(),
            ],
            'semester' => [
                'label' => $from->locale('id')->translatedFormat('M Y').' – '.$to->locale('id')->translatedFormat('M Y'),
                'term' => $this->term($from),
                'year' => $from->year.'/'.$to->year,
                'document' => 'SK-LAB-'.strtoupper((string) $prodi->organization?->code)
                    .'/'.$from->year.'/SMT-'.$this->term($from),
                'generated_at' => now()->locale('id')->translatedFormat('d F Y, H:i').' WIB',
            ],
            'prodi' => $prodi,
        ];
    }

    /**
     * @param  Collection<int, Booking>  $loans
     * @param  Collection<int, Booking>  $rejected
     * @param  Collection<int, Booking>  $bookings
     * @return array<string, mixed>
     */
    private function row(
        Category $category,
        Collection $loans,
        Collection $rejected,
        Collection $bookings,
        int $periodDays,
        Carbon $from,
        Carbon $to,
    ): array {
        $demandHours = $loans->sum(
            fn (Booking $booking): int => $booking->items
                ->filter(fn ($item): bool => $item->asset?->category_id === $category->id)
                ->count() * $booking->durationInDaysWithin($from, $to) * self::OPERATING_HOURS_PER_DAY,
        );

        $capacityHours = $category->assets_count * self::OPERATING_HOURS_PER_DAY * $periodDays;
        $requests = $bookings->filter(
            fn (Booking $booking): bool => $booking->items->contains(
                fn ($item): bool => $item->asset?->category_id === $category->id,
            ),
        )->count();
        $rejectedCount = $rejected->filter(
            fn (Booking $booking): bool => $booking->items->contains(
                fn ($item): bool => $item->asset?->category_id === $category->id,
            ),
        )->count();

        $rejectionRate = $requests > 0 ? round($rejectedCount / $requests * 100, 1) : 0.0;
        $utilization = $capacityHours > 0 ? round($demandHours / $capacityHours * 100, 1) : 0.0;

        return [
            'category' => $category,
            'units' => $category->assets_count,
            'demand_hours' => (int) $demandHours,
            'capacity_hours' => (int) $capacityHours,
            'utilization' => $utilization,
            'requests' => $requests,
            'rejected' => $rejectedCount,
            'rejection_rate' => $rejectionRate,
            'status' => $this->status($rejectionRate, $utilization),
        ];
    }

    private function status(float $rejectionRate, float $utilization): string
    {
        if ($rejectionRate >= 20 || $utilization >= 80) {
            return 'kritis';
        }

        if ($rejectionRate >= 10) {
            return 'perlu_tambahan';
        }

        return 'seimbang';
    }

    private function term(Carbon $from): string
    {
        return $from->month >= 8 ? 'GANJIL' : 'GENAP';
    }
}
