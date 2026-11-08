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
    }

    public function test_operator_menu_hides_administration_items(): void
    {
        $response = $this->actingAs($this->user('operator@lendora.test'))->get('/admin');

        $response->assertOk();
        $response->assertSee('Serah Terima');
        $response->assertDontSee('Log Aktivitas');
        $response->assertDontSee('Pengguna');
    }

    public function test_admin_prodi_menu_shows_reports_and_no_user_management(): void
    {
        $response = $this->actingAs($this->user('adminprodi@lendora.test'))->get('/admin');

        $response->assertOk();
        $response->assertSee('Laporan');
        $response->assertDontSee('Pengguna');
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
