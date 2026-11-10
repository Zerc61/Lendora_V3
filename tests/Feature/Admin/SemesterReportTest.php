<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterReportTest extends TestCase
{
    use RefreshDatabase;

    private User $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
    }

    public function test_prodi_sees_semester_report(): void
    {
        $this->actingAs($this->prodi)
            ->get(route('admin.reports.semester'))
            ->assertOk()
            ->assertSee(__('ui.semester_report.title'))
            ->assertSee(__('ui.semester_report.kpi_utilization'))
            ->assertSee(__('ui.semester_report.sign_title'))
            ->assertSee('SK-LAB-LDR/2026/SMT-GANJIL');
    }

    public function test_non_prodi_roles_are_forbidden(): void
    {
        foreach (['operator@lendora.test', 'admin@lendora.test', 'technician@lendora.test', 'borrower1@lendora.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail())
                ->get(route('admin.reports.semester'))
                ->assertForbidden();
        }
    }
}
