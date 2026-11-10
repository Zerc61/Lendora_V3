<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Organization;
use App\Models\User;
use App\Services\CourseQuotaService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseQuotaServiceTest extends TestCase
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

    public function test_quota_usage_and_status_per_course(): void
    {
        $normal = $this->course('DKV-201', 120);
        $critical = $this->course('DKV-312', 100);
        $unset = $this->course('DKV-999', null);

        $this->booking($normal, '2026-09-01', 3);
        $this->booking($critical, '2026-09-10', 4);

        $summary = app(CourseQuotaService::class)->for($this->prodi);

        $this->assertSame(220, $summary['kpis']['total_quota']);
        $this->assertSame(108, $summary['kpis']['total_used']);
        $this->assertSame(112, $summary['kpis']['remaining']);
        $this->assertSame(0, $summary['kpis']['critical']);

        $rows = $summary['courses']->keyBy(fn (array $row) => $row['course']->code);

        $this->assertSame(48, $rows['DKV-201']['used_hours']);
        $this->assertSame(72, $rows['DKV-201']['remaining_hours']);
        $this->assertSame(40.0, $rows['DKV-201']['percent']);
        $this->assertSame('surplus', $rows['DKV-201']['status']);

        $this->assertSame(60, $rows['DKV-312']['used_hours']);
        $this->assertSame(60.0, $rows['DKV-312']['percent']);
        $this->assertSame('normal', $rows['DKV-312']['status']);

        $this->assertNull($rows['DKV-999']['percent']);
        $this->assertSame('unset', $rows['DKV-999']['status']);
        $this->assertSame($critical->id, $summary['courses']->first()['course']->id);
        $this->assertNotNull($unset->id);
    }

    public function test_pending_and_out_of_semester_bookings_are_ignored(): void
    {
        $course = $this->course('DKV-201', 120);

        $this->booking($course, '2026-09-01', 3, BookingStatus::Pending);
        $this->booking($course, '2027-01-05', 3, BookingStatus::Approved);
        $this->booking($course, '2026-09-01', 3, null);

        $summary = app(CourseQuotaService::class)->for($this->prodi);

        $this->assertSame(0, $summary['kpis']['total_used']);
        $this->assertSame(0, $summary['kpis']['critical']);
    }

    public function test_same_day_booking_counts_as_one_day(): void
    {
        $course = $this->course('DKV-201', 120);
        $this->booking($course, '2026-09-01', 0);

        $summary = app(CourseQuotaService::class)->for($this->prodi);

        $this->assertSame(12, $summary['kpis']['total_used']);
    }

    public function test_booking_crossing_semester_boundary_is_clamped_to_period(): void
    {
        $course = $this->course('DKV-201', 120);
        $this->booking($course, '2026-12-30', 10);

        $summary = app(CourseQuotaService::class)->for($this->prodi);

        $this->assertSame(24, $summary['kpis']['total_used']);
    }

    private function course(string $code, ?int $quota): Course
    {
        return Course::create([
            'organization_id' => $this->organization->id,
            'code' => $code,
            'name' => 'Mata Kuliah '.$code,
            'lecturer_name' => 'Dosen Uji',
            'semester' => 3,
            'credits' => 3,
            'quota_hours' => $quota,
            'is_active' => true,
        ]);
    }

    private function booking(
        Course $course,
        string $start,
        int $days,
        ?BookingStatus $status = BookingStatus::Approved,
    ): Booking {
        $start = Carbon::parse($start);

        return Booking::create([
            'code' => 'LDR-BK-QTA-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'course_id' => $status === null ? null : $course->id,
            'status' => $status ?? BookingStatus::Pending,
            'purpose' => 'Uji kuota',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays($days),
        ]);
    }
}
