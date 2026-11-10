<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_organization_admin_sees_all_sidebar_groups(): void
    {
        $response = $this->actingAs($this->user('admin@lendora.test'))->get('/admin');

        $response->assertOk();
        $response->assertSee('css/lendora.css');
        $response->assertSee('css/admin.css');
        $response->assertSeeInOrder(['Inventaris', 'Transaksi', 'Operasional', 'Administrasi']);
        $response->assertSee('Log Aktivitas');
        $response->assertSee(route('admin.users.index'));
        $response->assertSee(route('admin.settings.edit'));
        $response->assertSee(route('admin.activity-logs.index'));
    }

    public function test_admin_pages_show_breadcrumb_but_dashboard_does_not(): void
    {
        $admin = $this->user('admin@lendora.test');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('aria-label="Breadcrumb"', false);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false);
    }

    public function test_operator_menu_hides_administration_items(): void    {
        $response = $this->actingAs($this->user('operator@lendora.test'))->get('/admin');

        $response->assertOk();
        $response->assertSee('Serah Terima');
        $response->assertDontSee('Log Aktivitas');
        $response->assertDontSee('Pengguna');
        $response->assertDontSee('Pengaturan');
        $response->assertSee(route('admin.handovers.index'));
        $response->assertSee(route('admin.inspections.index'));
        $response->assertSee(route('admin.maintenances.index'));
        $response->assertSee(route('admin.bookings.walk-in'));
    }

    public function test_admin_prodi_menu_shows_reports_and_no_user_management(): void
    {
        $response = $this->actingAs($this->user('adminprodi@lendora.test'))->get('/admin');

        $response->assertOk();
        $response->assertSee('Laporan');
        $response->assertSee(__('ui.nav.otorisasi_khusus'));
        $response->assertSee(__('ui.nav.dashboard_prodi'));
        $response->assertSee(__('ui.nav.students'));
        $response->assertSee(route('admin.students.index'));
        $response->assertSee(route('admin.bookings.approval'));
        $response->assertSee(route('admin.assets.index'));
        $response->assertDontSee('Pengguna');
        $response->assertDontSee('Log Aktivitas');
        $response->assertDontSee(route('admin.handovers.index'));
        $response->assertDontSee(route('admin.settings.edit'));
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
