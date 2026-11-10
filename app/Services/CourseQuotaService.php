<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\PracticumSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseQuotaService
{
    /**
     * Jam operasional asumsi per unit per hari. Sama dengan laporan semester.
     */
    private const OPERATING_HOURS_PER_DAY = 12;

    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    public function updateQuota(User $prodi, Course $course, ?int $hours): Course
    {
        $this->assertCourse($prodi, $course);

        return DB::transaction(function () use ($prodi, $course, $hours): Course {
            $locked = Course::query()->lockForUpdate()->findOrFail($course->id);
            $before = $locked->quota_hours;
            $locked->update(['quota_hours' => $hours]);

            $this->logger->log(
                'course_quota.updated',
                $locked,
                __('messages.course_quota.updated').' '.$locked->code,
                $prodi,
                ['before' => $before, 'after' => $hours],
            );

            return $locked;
        });
    }

    /**
     * @param  array{code: string, name: string, lecturer_name: ?string, semester: int, credits: int, quota_hours: ?int}  $data
     */
    public function createCourse(User $prodi, array $data): Course
    {
        $course = Course::create([
            'organization_id' => $prodi->organization_id,
            'code' => $data['code'],
            'name' => $data['name'],
            'lecturer_name' => $data['lecturer_name'] ?? null,
            'semester' => $data['semester'],
            'credits' => $data['credits'],
            'quota_hours' => $data['quota_hours'] ?? null,
            'is_active' => true,
        ]);

        $this->logger->log(
            'course.created',
            $course,
            __('messages.course_quota.created').' '.$course->code,
            $prodi,
        );

        return $course;
    }

    /**
     * @param  array{code: string, name: string, lecturer_name: ?string, semester: int, credits: int, quota_hours: ?int}  $data
     */
    public function updateCourse(User $prodi, Course $course, array $data): Course
    {
        $this->assertCourse($prodi, $course);

        return DB::transaction(function () use ($prodi, $course, $data): Course {
            $locked = Course::query()->lockForUpdate()->findOrFail($course->id);
            $before = $locked->only(['code', 'name', 'lecturer_name', 'semester', 'credits', 'quota_hours']);

            $locked->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'lecturer_name' => $data['lecturer_name'] ?? null,
                'semester' => $data['semester'],
                'credits' => $data['credits'],
                'quota_hours' => $data['quota_hours'] ?? null,
            ]);

            $this->logger->log(
                'course.updated',
                $locked,
                __('messages.course_quota.updated').' '.$locked->code,
                $prodi,
                ['before' => $before, 'after' => $locked->only(array_keys($before))],
            );

            return $locked;
        });
    }

    public function deleteCourse(User $prodi, Course $course): void
    {
        $this->assertCourse($prodi, $course);

        if ($course->bookings()->exists()) {
            throw ValidationException::withMessages([
                'course' => __('messages.course_quota.delete_has_bookings'),
            ]);
        }

        if (PracticumSchedule::query()->where('course_id', $course->id)->exists()) {
            throw ValidationException::withMessages([
                'course' => __('messages.course_quota.delete_has_schedules'),
            ]);
        }

        $course->delete();

        $this->logger->log(
            'course.deleted',
            $course,
            __('messages.course_quota.deleted').' '.$course->code,
            $prodi,
        );
    }

    public function transfer(User $prodi, Course $from, Course $to, int $hours): void
    {
        $this->assertCourse($prodi, $from);
        $this->assertCourse($prodi, $to);

        DB::transaction(function () use ($prodi, $from, $to, $hours): void {
            $courses = Course::query()
                ->whereIn('id', [$from->id, $to->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $source = $courses[$from->id];
            $target = $courses[$to->id];

            if ($source->quota_hours === null || $source->quota_hours < $hours) {
                throw ValidationException::withMessages([
                    'hours' => __('messages.course_quota.transfer_insufficient'),
                ]);
            }

            $source->update(['quota_hours' => $source->quota_hours - $hours]);
            $target->update(['quota_hours' => (int) $target->quota_hours + $hours]);

            $this->logger->log(
                'course_quota.transferred',
                $target,
                __('messages.course_quota.transferred').' '.$source->code.' → '.$target->code,
                $prodi,
                ['from' => $source->code, 'to' => $target->code, 'hours' => $hours],
            );
        });
    }

    private function assertCourse(User $prodi, Course $course): void
    {
        if ($course->organization_id !== $prodi->organization_id) {
            throw ValidationException::withMessages([
                'course_id' => __('messages.course_quota.wrong_organization'),
            ]);
        }
    }

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
