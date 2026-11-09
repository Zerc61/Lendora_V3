<?php

namespace App\Services;

use App\Models\User;
use App\Services\SettingService;
use Carbon\CarbonInterface;

class QuotaService
{
    public function summary(User $user): array
    {
        $semesterStart = app(SettingService::class)->get('semester_start', '2026-08-01');
        $semesterEnd = app(SettingService::class)->get('semester_end', '2026-12-31');
        $quotaHours = (int) app(SettingService::class)->get('semester_quota_hours', 25);

        $usedHours = \App\Models\Booking::query()
            ->where('borrower_id', $user->id)
            ->whereIn('status', ['approved', 'active', 'returned'])
            ->whereBetween('requested_start_date', [$semesterStart, $semesterEnd])
            ->get()
            ->sum(fn ($booking) => $booking->requested_start_date->diffInHours($booking->requested_end_date));

        $remaining = max(0, $quotaHours - $usedHours);

        return [
            'total_hours' => $quotaHours,
            'used_hours' => $usedHours,
            'remaining_hours' => $remaining,
            'percentage' => $quotaHours > 0 ? round(($usedHours / $quotaHours) * 100) : 0,
        ];
    }

    public function assertCanBorrow(User $user, array $assetIds, CarbonInterface $start, CarbonInterface $end): void
    {
        $summary = $this->summary($user);
        $requestedHours = $start->diffInHours($end);

        if ($summary['remaining_hours'] < $requestedHours) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], [])->addError('quota', __('ui.booking.quota_exceeded'))
            );
        }
    }
}