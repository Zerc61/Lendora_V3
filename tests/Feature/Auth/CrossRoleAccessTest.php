<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_borrower_cannot_open_admin_area(): void
    {
        $this->actingAs($this->user('borrower1@lendora.test'))
            ->get('/admin')
            ->assertForbidden()
            ->assertSee('Akses ditolak');
    }

    public function test_technician_can_open_admin_dashboard(): void
    {
        $this->actingAs($this->user('technician@lendora.test'))
            ->get('/admin')
            ->assertOk();
    }

    public function test_operator_cannot_open_borrower_area(): void
    {
        $this->actingAs($this->user('operator@lendora.test'))
            ->get('/borrower')
            ->assertForbidden();
    }

    public function test_borrower_can_open_borrower_home(): void
    {
        $this->actingAs($this->user('borrower1@lendora.test'))
            ->get('/borrower')
            ->assertOk();
    }

    public function test_operator_and_organization_admin_can_open_admin_dashboard(): void
    {
        $this->actingAs($this->user('operator@lendora.test'))->get('/admin')->assertOk();
        $this->actingAs($this->user('admin@lendora.test'))->get('/admin')->assertOk();
        $this->actingAs($this->user('adminprodi@lendora.test'))->get('/admin')->assertOk();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
