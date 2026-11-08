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
    public function conflictsFor(Asset $asset, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return Booking::query()
            ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Active->value])
            ->whereDate('requested_start_date', '<', $end->toDateString())
            ->whereDate('requested_end_date', '>', $start->toDateString())
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
}
