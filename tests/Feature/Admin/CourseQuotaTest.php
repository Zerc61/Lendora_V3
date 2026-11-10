<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseQuotaTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $prodi;

    private User $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
    }

    public function test_prodi_sees_course_quota_utilization(): void
    {
        $course = $this->course('DKV-312', 100);

        $start = Carbon::parse('2026-09-10');
        Booking::create([
            'code' => 'LDR-BK-CRS-0001',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'course_id' => $course->id,
            'status' => BookingStatus::Approved,
            'purpose' => 'Praktikum sinematografi',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays(4),
        ]);

        $this->actingAs($this->prodi)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee(__('ui.course_quota.title'))
            ->assertSee('DKV-312')
            ->assertSee('Sinematografi Uji')
            ->assertSee('Dosen Uji')
            ->assertSee(__('ui.course_quota.used_of', ['used' => 60, 'quota' => 100]))
            ->assertSee(__('ui.course_quota.status_normal'))
            ->assertSee(__('ui.course_quota.kpi_total'));
    }

    public function test_non_prodi_roles_are_forbidden(): void
    {
        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get(route('admin.courses.index'))
                ->assertForbidden();
        }
    }

    public function test_prodi_updates_course_quota(): void
    {
        $course = $this->course('DKV-312', 40);

        $this->actingAs($this->prodi)
            ->put(route('admin.courses.quota.update', $course), ['quota_hours' => 90])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('status', __('messages.course_quota.updated'));

        $this->assertSame(90, $course->fresh()->quota_hours);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'course_quota.updated',
            'subject_id' => $course->id,
            'user_id' => $this->prodi->id,
        ]);
    }

    public function test_quota_update_rejects_invalid_value(): void
    {
        $course = $this->course('DKV-312', 40);

        $this->actingAs($this->prodi)
            ->put(route('admin.courses.quota.update', $course), ['quota_hours' => 0])
            ->assertSessionHasErrors('quota_hours');

        $this->assertSame(40, $course->fresh()->quota_hours);
    }

    public function test_prodi_transfers_quota_between_courses(): void
    {
        $from = $this->course('DKV-312', 40);
        $to = $this->course('DKV-313', 10);

        $this->actingAs($this->prodi)
            ->post(route('admin.courses.transfer'), [
                'from_course_id' => $from->id,
                'to_course_id' => $to->id,
                'hours' => 15,
            ])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('status', __('messages.course_quota.transferred'));

        $this->assertSame(25, $from->fresh()->quota_hours);
        $this->assertSame(25, $to->fresh()->quota_hours);
    }

    public function test_transfer_fails_when_source_quota_insufficient(): void
    {
        $from = $this->course('DKV-312', 5);
        $to = $this->course('DKV-313', 10);

        $this->actingAs($this->prodi)
            ->post(route('admin.courses.transfer'), [
                'from_course_id' => $from->id,
                'to_course_id' => $to->id,
                'hours' => 20,
            ])
            ->assertSessionHasErrors('hours');

        $this->assertSame(5, $from->fresh()->quota_hours);
        $this->assertSame(10, $to->fresh()->quota_hours);
    }

    public function test_transfer_to_same_course_is_rejected(): void
    {
        $course = $this->course('DKV-312', 40);

        $this->actingAs($this->prodi)
            ->post(route('admin.courses.transfer'), [
                'from_course_id' => $course->id,
                'to_course_id' => $course->id,
                'hours' => 5,
            ])
            ->assertSessionHasErrors('to_course_id');
    }

    public function test_write_routes_forbidden_for_non_prodi(): void
    {
        $course = $this->course('DKV-312', 40);

        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test'] as $email) {
            $actor = User::where('email', $email)->firstOrFail();

            $this->actingAs($actor)
                ->put(route('admin.courses.quota.update', $course), ['quota_hours' => 99])
                ->assertForbidden();

            $this->actingAs($actor)
                ->post(route('admin.courses.transfer'), [
                    'from_course_id' => $course->id,
                    'to_course_id' => $course->id + 1,
                    'hours' => 1,
                ])
                ->assertForbidden();
        }

        $this->assertSame(40, $course->fresh()->quota_hours);
    }

    private function course(string $code, int $quota): Course
    {
        return Course::create([
            'organization_id' => $this->organization->id,
            'code' => $code,
            'name' => 'Sinematografi Uji',
            'lecturer_name' => 'Dosen Uji',
            'semester' => 5,
            'credits' => 4,
            'quota_hours' => $quota,
            'is_active' => true,
        ]);
    }
}
