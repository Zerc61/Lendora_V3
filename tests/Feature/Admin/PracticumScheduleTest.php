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

    public function test_prodi_plots_new_schedule(): void
    {
        $room = $this->room('R-01');

        $this->actingAs($this->prodi)
            ->post(route('admin.practicum.store'), [
                'course_id' => $this->course->id,
                'room_id' => $room->id,
                'day_of_week' => 2,
                'start_time' => '09:00',
                'end_time' => '11:00',
            ])
            ->assertRedirect(route('admin.practicum.index'))
            ->assertSessionHas('status', __('messages.practicum.created'));

        $this->assertDatabaseHas('practicum_schedules', [
            'course_id' => $this->course->id,
            'room_id' => $room->id,
            'day_of_week' => 2,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);
    }

    public function test_plot_rejects_overlapping_slot(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, '08:00', '11:00');

        $this->actingAs($this->prodi)
            ->post(route('admin.practicum.store'), [
                'course_id' => $this->course->id,
                'room_id' => $room->id,
                'day_of_week' => 1,
                'start_time' => '10:00',
                'end_time' => '12:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, PracticumSchedule::count());
    }

    public function test_plot_rejects_room_from_another_organization(): void
    {
        $other = Organization::create(['name' => 'Org Lain', 'code' => 'OTH']);

        $foreignRoom = Room::create([
            'organization_id' => $other->id,
            'code' => 'R-X',
            'name' => 'Studio Asing',
            'capacity' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->prodi)
            ->post(route('admin.practicum.store'), [
                'course_id' => $this->course->id,
                'room_id' => $foreignRoom->id,
                'day_of_week' => 1,
                'start_time' => '08:00',
                'end_time' => '10:00',
            ])
            ->assertSessionHasErrors('room_id');

        $this->assertSame(0, PracticumSchedule::count());
    }

    public function test_prodi_deletes_schedule(): void
    {
        $room = $this->room('R-01');
        $schedule = $this->schedule($room, '08:00', '11:00');

        $this->actingAs($this->prodi)
            ->delete(route('admin.practicum.destroy', $schedule))
            ->assertRedirect(route('admin.practicum.index'))
            ->assertSessionHas('status', __('messages.practicum.deleted'));

        $this->assertDatabaseMissing('practicum_schedules', ['id' => $schedule->id]);
    }

    public function test_write_routes_forbidden_for_non_prodi(): void
    {
        $room = $this->room('R-01');
        $schedule = $this->schedule($room, '08:00', '11:00');

        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $actor = User::where('email', $email)->firstOrFail();

            $this->actingAs($actor)
                ->post(route('admin.practicum.store'), [
                    'course_id' => $this->course->id,
                    'room_id' => $room->id,
                    'day_of_week' => 2,
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                ])
                ->assertForbidden();

            $this->actingAs($actor)
                ->delete(route('admin.practicum.destroy', $schedule))
                ->assertForbidden();
        }

        $this->assertSame(1, PracticumSchedule::count());
    }

    public function test_prodi_creates_room(): void
    {
        $this->actingAs($this->prodi)
            ->post(route('admin.rooms.store'), [
                'code' => 'R-02',
                'name' => 'Studio R-02',
                'capacity' => 25,
                'notes' => 'Lantai 2',
            ])
            ->assertRedirect(route('admin.practicum.index'))
            ->assertSessionHas('status', __('messages.practicum.room_created'));

        $this->assertDatabaseHas('rooms', [
            'organization_id' => $this->organization->id,
            'code' => 'R-02',
            'capacity' => 25,
            'is_active' => true,
        ]);
    }

    public function test_room_code_must_be_unique_per_organization(): void
    {
        $this->room('R-01');

        $this->actingAs($this->prodi)
            ->post(route('admin.rooms.store'), [
                'code' => 'R-01',
                'name' => 'Duplikat',
            ])
            ->assertSessionHasErrors('code');

        $this->assertSame(1, Room::count());
    }

    public function test_prodi_updates_room(): void
    {
        $room = $this->room('R-01');

        $this->actingAs($this->prodi)
            ->put(route('admin.rooms.update', $room), [
                'code' => 'R-09',
                'name' => 'Studio Baru',
                'capacity' => 40,
                'notes' => 'Renovasi',
            ])
            ->assertRedirect(route('admin.practicum.index'));

        $room->refresh();

        $this->assertSame('R-09', $room->code);
        $this->assertSame('Studio Baru', $room->name);
        $this->assertSame(40, $room->capacity);
    }

    public function test_prodi_deletes_empty_room(): void
    {
        $room = $this->room('R-01');

        $this->actingAs($this->prodi)
            ->delete(route('admin.rooms.destroy', $room))
            ->assertRedirect(route('admin.practicum.index'))
            ->assertSessionHas('status', __('messages.practicum.room_deleted'));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_room_with_schedule_cannot_be_deleted(): void
    {
        $room = $this->room('R-01');
        $this->schedule($room, '08:00', '11:00');

        $this->actingAs($this->prodi)
            ->delete(route('admin.rooms.destroy', $room))
            ->assertSessionHasErrors('room');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_room_routes_forbidden_for_non_prodi(): void
    {
        $room = $this->room('R-01');

        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $actor = User::where('email', $email)->firstOrFail();

            $this->actingAs($actor)
                ->post(route('admin.rooms.store'), ['code' => 'R-X', 'name' => 'X'])
                ->assertForbidden();

            $this->actingAs($actor)
                ->put(route('admin.rooms.update', $room), ['code' => 'R-01', 'name' => 'X'])
                ->assertForbidden();

            $this->actingAs($actor)
                ->delete(route('admin.rooms.destroy', $room))
                ->assertForbidden();
        }

        $this->assertSame(1, Room::count());
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
