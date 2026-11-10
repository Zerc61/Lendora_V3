<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Organization;
use App\Models\PracticumSchedule;
use App\Models\Room;
use App\Models\User;
use App\Services\PracticumScheduleService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticumScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $prodi;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->course = Course::create([
            'organization_id' => $this->organization->id,
            'code' => 'DKV-201',
            'name' => 'Fotografi Dasar',
            'lecturer_name' => 'Rina Kusuma, M.Ds.',
            'semester' => 3,
            'credits' => 3,
            'is_active' => true,
        ]);
    }

    public function test_overlapping_slots_on_same_room_and_day_are_conflicts(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, 1, '08:00', '11:00');
        $this->schedule($room, 1, '10:00', '12:00');

        $summary = app(PracticumScheduleService::class)->for($this->prodi);

        $this->assertSame(1, $summary['kpis']['conflicts']);
        $this->assertSame(1, $summary['kpis']['rooms']);
        $this->assertSame(5.0, $summary['kpis']['scheduled_hours']);
        $this->assertSame(1, $summary['conflicts']->count());
        $this->assertSame($room->id, $summary['conflicts']->first()['room']->id);
    }

    public function test_every_overlapping_pair_is_detected(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, 1, '08:00', '12:00');
        $this->schedule($room, 1, '09:00', '10:00');
        $this->schedule($room, 1, '09:30', '09:45');

        $summary = app(PracticumScheduleService::class)->for($this->prodi);

        $this->assertSame(3, $summary['kpis']['conflicts']);
    }

    public function test_adjacent_slots_do_not_conflict_and_inactive_are_ignored(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, 1, '08:00', '10:00');
        $this->schedule($room, 1, '10:00', '12:00');
        $this->schedule($room, 1, '09:00', '11:00', false);

        $summary = app(PracticumScheduleService::class)->for($this->prodi);

        $this->assertSame(0, $summary['kpis']['conflicts']);
        $this->assertSame(4.0, $summary['kpis']['scheduled_hours']);
    }

    public function test_same_slot_in_different_rooms_is_not_a_conflict(): void
    {
        $first = $this->room('R-01');
        $second = $this->room('R-02');

        $this->schedule($first, 2, '09:00', '12:00');
        $this->schedule($second, 2, '09:00', '12:00');

        $summary = app(PracticumScheduleService::class)->for($this->prodi);

        $this->assertSame(0, $summary['kpis']['conflicts']);
        $this->assertSame(6.0, $summary['kpis']['scheduled_hours']);
    }

    private function room(string $code): Room
    {
        return Room::create([
            'organization_id' => $this->organization->id,
            'code' => $code,
            'name' => 'Studio '.$code,
            'capacity' => 30,
            'is_active' => true,
        ]);
    }

    private function schedule(Room $room, int $day, string $start, string $end, bool $active = true): PracticumSchedule
    {
        return PracticumSchedule::create([
            'organization_id' => $this->organization->id,
            'course_id' => $this->course->id,
            'room_id' => $room->id,
            'day_of_week' => $day,
            'start_time' => $start.':00',
            'end_time' => $end.':00',
            'is_active' => $active,
        ]);
    }
}
