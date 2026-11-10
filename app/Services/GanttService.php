<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\AssetBlock;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class GanttService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $organizationId, CarbonInterface $start, int $days, ?int $categoryId = null): array
    {
        $days = max(1, min(60, $days));
        $start = Carbon::parse($start)->startOfDay();
        $end = $start->copy()->addDays($days - 1);

        $assets = Asset::query()
            ->with('category')
            ->where('organization_id', $organizationId)
            ->when($categoryId, fn ($query, int $id) => $query->where('category_id', $id))
            ->orderBy('code')
            ->get();

        $assetIds = $assets->pluck('id');

        $bookings = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
            ])
            ->whereDate('requested_start_date', '<=', $end->toDateString())
            ->whereDate('requested_end_date', '>=', $start->toDateString())
            ->with(['items' => fn ($query) => $query->whereIn('asset_id', $assetIds)])
            ->get();

        $blocks = AssetBlock::query()
            ->whereIn('asset_id', $assetIds)
            ->where('starts_at', '<=', $end->copy()->endOfDay())
            ->where('ends_at', '>=', $start)
            ->get();

        $bars = [];

        foreach ($bookings as $booking) {
            foreach ($booking->items as $item) {
                $bars[$item->asset_id][] = $this->bar(
                    (int) $item->asset_id,
                    $start,
                    $end,
                    $booking->requested_start_date,
                    $booking->requested_end_date,
                    [
                        'type' => 'booking',
                        'status' => $booking->status->value,
                        'code' => $booking->code,
                        'booking_id' => $booking->id,
                    ],
                );
            }
        }

        foreach ($blocks as $block) {
            $bars[$block->asset_id][] = $this->bar(
                (int) $block->asset_id,
                $start,
                $end,
                $block->starts_at,
                $block->ends_at,
                [
                    'type' => 'block',
                    'status' => $block->type->value,
                    'code' => $block->reason ?: ucfirst($block->type->value),
                    'booking_id' => null,
                ],
            );
        }

        $rows = $assets->map(function (Asset $asset) use (&$bars, $start, $end, $days): array {
            $assetBars = collect($bars[$asset->id] ?? [])
                ->filter()
                ->sortBy('offset')
                ->values();

            if ($asset->status === AssetStatus::Maintenance && $assetBars->where('type', 'block')->isEmpty()) {
                $assetBars->push($this->bar(
                    (int) $asset->id,
                    $start,
                    $end,
                    $start,
                    $end,
                    ['type' => 'block', 'status' => 'maintenance', 'code' => __('ui.gantt.maintenance'), 'booking_id' => null],
                ));
            }

            $conflict = $this->hasOverlap($assetBars->where('type', 'booking'));

            $assetBars = $assetBars->map(fn (array $bar): array => $bar + ['conflict' => $conflict]);

            return [
                'asset' => $asset,
                'bars' => $assetBars,
                'conflict' => $conflict,
            ];
        })->values();

        $occupiedDays = $rows->sum(fn (array $row): int => $row['bars']->sum('length'));
        $capacity = max(1, $rows->count() * $days);

        return [
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'rows' => $rows,
            'kpis' => [
                'occupancy' => round($occupiedDays / $capacity * 100, 1),
                'active_outside' => Booking::query()
                    ->where('organization_id', $organizationId)
                    ->where('status', BookingStatus::Active->value)
                    ->count(),
                'pending_handover' => Booking::query()
                    ->where('organization_id', $organizationId)
                    ->where('status', BookingStatus::Approved->value)
                    ->whereBetween('requested_start_date', [$start->toDateString(), $end->toDateString()])
                    ->count(),
                'conflicts' => $rows->where('conflict', true)->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>|null
     */
    private function bar(int $assetId, Carbon $start, Carbon $end, CarbonInterface $from, CarbonInterface $to, array $extra): ?array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->startOfDay();
        $rangeEnd = $end->copy();

        if ($to->lt($start) || $from->gt($rangeEnd)) {
            return null;
        }

        $from = $from->lt($start) ? $start->copy() : $from;
        $to = $to->gt($rangeEnd) ? $rangeEnd->copy() : $to;

        return $extra + [
            'asset_id' => $assetId,
            'offset' => (int) $start->diffInDays($from),
            'length' => max(1, (int) $from->diffInDays($to) + 1),
            'start' => $from->toDateString(),
            'end' => $to->toDateString(),
        ];
    }

    private function hasOverlap(\Illuminate\Support\Collection $bars): bool
    {
        $bars = $bars->sortBy('offset')->values();

        for ($i = 1; $i < $bars->count(); $i++) {
            $previousEnd = (int) $bars[$i - 1]['offset'] + (int) $bars[$i - 1]['length'];
            if ((int) $bars[$i]['offset'] < $previousEnd) {
                return true;
            }
        }

        return false;
    }
}
