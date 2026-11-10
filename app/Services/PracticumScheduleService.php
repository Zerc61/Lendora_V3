<?php

namespace App\Services;

use App\Models\Course;
use App\Models\PracticumSchedule;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PracticumScheduleService
{
    /**
     * Jam operasional asumsi: 08:00–20:00, Senin–Jumat.
     */
    private const OPERATING_HOURS_PER_DAY = 12;

    private const OPERATING_DAYS = 5;

    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    /**
     * @return array{courses: Collection<int, Course>, rooms: Collection<int, Room>}
     */
    public function options(User $prodi): array
    {
        return [
            'courses' => Course::query()
                ->where('organization_id', $prodi->organization_id)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
            'rooms' => Room::query()
                ->where('organization_id', $prodi->organization_id)
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
        ];
    }

    /**
     * @param  array{course_id: int, room_id: int, day_of_week: int, start_time: string, end_time: string}  $data
     */
    public function create(User $prodi, array $data): PracticumSchedule
    {
        return DB::transaction(function () use ($prodi, $data): PracticumSchedule {
            Room::query()
                ->where('organization_id', $prodi->organization_id)
                ->whereKey($data['room_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $clash = PracticumSchedule::query()
                ->with('course')
                ->where('room_id', $data['room_id'])
                ->where('day_of_week', $data['day_of_week'])
                ->where('is_active', true)
                ->where('start_time', '<', $data['end_time'].':00')
                ->where('end_time', '>', $data['start_time'].':00')
                ->first();

            if ($clash !== null) {
                throw ValidationException::withMessages([
                    'start_time' => __('messages.practicum.conflict', [
                        'course' => $clash->course?->code ?? '—',
                        'start' => substr($clash->start_time, 0, 5),
                        'end' => substr($clash->end_time, 0, 5),
                    ]),
                ]);
            }

            $schedule = PracticumSchedule::create([
                'organization_id' => $prodi->organization_id,
                'course_id' => $data['course_id'],
                'room_id' => $data['room_id'],
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'].':00',
                'end_time' => $data['end_time'].':00',
                'is_active' => true,
            ]);

            $this->logger->log(
                'practicum.created',
                $schedule,
                __('messages.practicum.created'),
                $prodi,
                $data,
            );

            return $schedule;
        });
    }

    public function remove(User $prodi, PracticumSchedule $schedule): void
    {
        if ($schedule->organization_id !== $prodi->organization_id) {
            throw ValidationException::withMessages([
                'schedule' => __('messages.practicum.wrong_organization'),
            ]);
        }

        $schedule->delete();

        $this->logger->log(
            'practicum.deleted',
            $schedule,
            __('messages.practicum.deleted'),
            $prodi,
            ['course_id' => $schedule->course_id, 'room_id' => $schedule->room_id],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function for(User $prodi): array
    {
        $organizationId = $prodi->organization_id;

        $rooms = Room::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->with(['schedules' => fn ($query) => $query
                ->where('is_active', true)
                ->with('course')
                ->orderBy('day_of_week')
                ->orderBy('start_time')])
            ->orderBy('code')
            ->get();

        $schedules = $rooms->flatMap->schedules;
        $conflicts = $this->conflicts($rooms);

        $scheduledHours = round($schedules->sum(fn (PracticumSchedule $schedule): float => $this->durationInHours($schedule)), 1);
        $capacity = $rooms->count() * self::OPERATING_HOURS_PER_DAY * self::OPERATING_DAYS;

        $semesterStart = (string) $this->settings->get('semester_start', '2026-08-01', $organizationId);
        $semesterEnd = (string) $this->settings->get('semester_end', '2026-12-31', $organizationId);

        return [
            'rooms' => $rooms,
            'conflicts' => $conflicts,
            'kpis' => [
                'rooms' => $rooms->count(),
                'scheduled_hours' => $scheduledHours,
                'capacity' => $capacity,
                'percent' => $capacity > 0 ? round($scheduledHours / $capacity * 100, 1) : 0,
                'conflicts' => $conflicts->count(),
            ],
            'semester' => [
                'label' => Carbon::parse($semesterStart)->locale('id')->translatedFormat('M Y')
                    .' – '
                    .Carbon::parse($semesterEnd)->locale('id')->translatedFormat('M Y'),
            ],
        ];
    }

    /**
     * @return Collection<int, array{room: Room, day: int, first: PracticumSchedule, second: PracticumSchedule}>
     */
    private function conflicts(Collection $rooms): Collection
    {
        return $rooms
            ->flatMap(function (Room $room): Collection {
                return $room->schedules
                    ->groupBy('day_of_week')
                    ->flatMap(fn (Collection $schedules): array => $this->overlaps($room, $schedules));
            })
            ->values();
    }

    /**
     * @param  Collection<int, PracticumSchedule>  $schedules
     * @return array<int, array{room: Room, day: int, first: PracticumSchedule, second: PracticumSchedule}>
     */
    private function overlaps(Room $room, Collection $schedules): array
    {
        $sorted = $schedules->sortBy('start_time')->values();
        $conflicts = [];

        foreach ($sorted as $index => $current) {
            foreach ($sorted->slice($index + 1) as $next) {
                if (substr($next->start_time, 0, 5) < substr($current->end_time, 0, 5)) {
                    $conflicts[] = [
                        'room' => $room,
                        'day' => $current->day_of_week,
                        'first' => $current,
                        'second' => $next,
                    ];
                }
            }
        }

        return $conflicts;
    }

    private function durationInHours(PracticumSchedule $schedule): float
    {
        $start = Carbon::parse('2000-01-01 '.$schedule->start_time);
        $end = Carbon::parse('2000-01-01 '.$schedule->end_time);

        return $start->diffInMinutes($end) / 60;
    }
}
