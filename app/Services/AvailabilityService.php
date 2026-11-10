<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class AvailabilityService
{
    public function isAvailableForRange(Asset $asset, CarbonInterface $start, CarbonInterface $end): bool
    {
        return $asset->status === AssetStatus::Available
            && ! $this->conflictsFor($asset, $start, $end)->exists();
    }

    /**
     * Bookings that already hold the asset on a range overlapping [start, end).
     *
     * @return Builder<Booking>
     */
    public function conflictsFor(Asset $asset, CarbonInterface $start, CarbonInterface $end, ?int $ignoreBookingId = null): Builder
    {
        return Booking::query()
            ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Active->value])
            ->whereDate('requested_start_date', '<', $end->toDateString())
            ->whereDate('requested_end_date', '>', $start->toDateString())
            ->when($ignoreBookingId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreBookingId))
            ->whereHas('items', fn (Builder $query) => $query->where('asset_id', $asset->id));
    }

    public function suggestFreeRange(Asset $asset, CarbonInterface $start, CarbonInterface $end): ?CarbonInterface
    {
        $duration = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        $candidate = $end->copy()->startOfDay();

        for ($attempt = 0; $attempt < 90; $attempt++) {
            $candidateEnd = $candidate->copy()->addDays($duration);

            if (! $this->conflictsFor($asset, $candidate, $candidateEnd)->exists()) {
                return $candidate;
            }

            $candidate->addDay();
        }

        return null;
    }

    public function calendarFor(Asset $asset, CarbonInterface $start, int $days): array
    {
        $calendar = [];
        $blocks = \App\Models\AssetBlock::where('asset_id', $asset->id)
            ->where('starts_at', '<=', $start->copy()->addDays($days)->endOfDay())
            ->where('ends_at', '>=', $start)
            ->get();

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $status = 'available';

            foreach ($blocks as $block) {
                if ($block->starts_at <= $dayEnd && $block->ends_at >= $dayStart) {
                    $status = $block->type === 'practical' ? 'practical' : 'booked';
                    break;
                }
            }

            if ($status === 'available' && ! $this->conflictsFor($asset, $dayStart, $dayEnd)->exists()) {
                $status = 'available';
            } elseif ($status !== 'practical') {
                $status = 'booked';
            }

            $calendar[] = [
                'date' => $day,
                'status' => $status,
                'label' => match ($status) {
                    'available' => __('ui.catalog.availability_available'),
                    'booked' => __('ui.catalog.availability_booked'),
                    'practical' => __('ui.catalog.availability_practical'),
                    default => __('ui.catalog.availability_unavailable'),
                },
            ];
        }

        return $calendar;
    }

    public function availabilityMapFor(\Illuminate\Support\Collection $assets, CarbonInterface $start, int $days): array
    {
        $map = [];

        foreach ($assets as $asset) {
            $map[$asset->id] = $this->calendarFor($asset, $start, $days);
        }

        return $map;
    }
}
