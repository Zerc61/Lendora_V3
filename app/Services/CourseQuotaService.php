<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CourseQuotaService
{
    /**
     * Jam operasional asumsi per unit per hari. Sama dengan laporan semester.
     */
    private const OPERATING_HOURS_PER_DAY = 12;

    public function __construct(private readonly SettingService $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function for(User $prodi): array
    {
        $organizationId = $prodi->organization_id;
        $semesterStart = (string) $this->settings->get('semester_start', '2026-08-01', $organizationId);
        $semesterEnd = (string) $this->settings->get('semester_end', '2026-12-31', $organizationId);

        $usage = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('course_id')
            ->whereIn('status', [
                BookingStatus::Approved->value,
                BookingStatus::Active->value,
                BookingStatus::Returned->value,
            ])
            ->whereDate('requested_start_date', '<=', $semesterEnd)
            ->whereDate('requested_end_date', '>=', $semesterStart)
            ->get(['course_id', 'requested_start_date', 'requested_end_date'])
            ->groupBy('course_id')
            ->map(fn (Collection $bookings): array => [
                'sessions' => $bookings->count(),
                'hours' => (int) $bookings->sum(
                    fn (Booking $booking): int => $booking->durationInDaysWithin(
                        Carbon::parse($semesterStart),
                        Carbon::parse($semesterEnd),
                    ) * self::OPERATING_HOURS_PER_DAY,
                ),
            ]);

        $courses = Course::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function (Course $course) use ($usage): array {
                $used = $usage[$course->id]['hours'] ?? 0;
                $quota = $course->quota_hours;
                $percent = $quota ? round($used / $quota * 100, 1) : null;

                return [
                    'course' => $course,
                    'sessions' => $usage[$course->id]['sessions'] ?? 0,
                    'used_hours' => $used,
                    'quota_hours' => $quota,
                    'remaining_hours' => $quota !== null ? max(0, $quota - $used) : null,
                    'percent' => $percent,
                    'status' => $this->statusFor($percent),
                ];
            })
            ->sortByDesc(fn (array $row): float => $row['percent'] ?? -1)
            ->values();

        $totalQuota = (int) $courses->sum('quota_hours');
        $totalUsed = (int) $courses->sum('used_hours');

        return [
            'courses' => $courses,
            'kpis' => [
                'total_quota' => $totalQuota,
                'total_used' => $totalUsed,
                'remaining' => max(0, $totalQuota - $totalUsed),
                'percent' => $totalQuota > 0 ? round($totalUsed / $totalQuota * 100, 1) : 0,
                'critical' => $courses->where('status', 'kritis')->count(),
            ],
            'semester' => [
                'label' => Carbon::parse($semesterStart)->locale('id')->translatedFormat('M Y')
                    .' – '
                    .Carbon::parse($semesterEnd)->locale('id')->translatedFormat('M Y'),
            ],
        ];
    }

    private function statusFor(?float $percent): string
    {
        if ($percent === null) {
            return 'unset';
        }

        if ($percent > 85) {
            return 'kritis';
        }

        if ($percent >= 50) {
            return 'normal';
        }

        return 'surplus';
    }
}
