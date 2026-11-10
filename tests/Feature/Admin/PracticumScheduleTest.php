<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Organization;
use App\Models\PracticumSchedule;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticumScheduleTest extends TestCase
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

    public function test_prodi_sees_weekly_schedule_and_conflict_alert(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, '08:00', '11:00');
        $this->schedule($room, '10:00', '12:00');

        $this->actingAs($this->prodi)
            ->get(route('admin.practicum.index'))
            ->assertOk()
            ->assertSee(__('ui.practicum.title'))
            ->assertSee('Studio R-01')
            ->assertSee('DKV-201 · Fotografi Dasar')
            ->assertSee(__('ui.practicum.conflict_title'))
            ->assertSee(__('ui.practicum.legend_conflict'))
            ->assertSee(__('ui.practicum.kpi_rooms'));
    }

    public function test_schedule_without_overlap_has_no_conflict_alert(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, '08:00', '11:00');

        $this->actingAs($this->prodi)
            ->get(route('admin.practicum.index'))
            ->assertOk()
            ->assertDontSee(__('ui.practicum.conflict_title'));
    }

    public function test_non_prodi_roles_are_forbidden(): void
    {
        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get(route('admin.practicum.index'))
                ->assertForbidden();
        }
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

    private function schedule(Room $room, string $start, string $end): PracticumSchedule
    {
        return PracticumSchedule::create([
            'organization_id' => $this->organization->id,
            'course_id' => $this->course->id,
            'room_id' => $room->id,
            'day_of_week' => 1,
            'start_time' => $start.':00',
            'end_time' => $end.':00',
            'is_active' => true,
        ]);
    }
}
