<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportFormatTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
    }

    public function test_report_exports_to_xlsx(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload();

        $this->assertGreaterThan(1000, $response->baseResponse->getFile()->getSize());
    }

    public function test_report_exports_to_pdf(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload();

        $this->assertGreaterThan(2000, strlen((string) $response->getContent()));
    }

    public function test_semester_report_exports_to_xlsx_and_pdf(): void
    {
        $prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();

        $this->actingAs($prodi)
            ->get(route('admin.reports.semester.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload();

        $this->actingAs($prodi)
            ->get(route('admin.reports.semester.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload();
    }

    public function test_asset_and_activity_log_exports_to_xlsx(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.assets.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload();

        $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload();
    }

    public function test_calendar_exports_to_xlsx(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.calendar.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertDownload();
    }

    public function test_borrower_cannot_download_admin_exports(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)
            ->get(route('admin.reports.pdf'))
            ->assertForbidden();
    }
}
