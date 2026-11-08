<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
    }

    public function test_organization_admin_creates_operator_who_can_log_in(): void
    {
        $operatorRole = Role::where('slug', 'operator')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Operator Baru',
                'email' => 'operator.baru@lendora.test',
                'phone' => '08123456789',
                'role_id' => $operatorRole->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'operator.baru@lendora.test',
            'role_id' => $operatorRole->id,
            'organization_id' => $this->admin->organization_id,
        ]);

        auth()->logout();

        $this->post('/login', [
            'email' => 'operator.baru@lendora.test',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_suspended_user_is_blocked_from_admin_area(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.suspend', $this->operator))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('suspended', $this->operator->fresh()->status);

        $this->actingAs($this->operator->fresh())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_role_change_is_logged(): void
    {
        $technicianRole = Role::where('slug', 'technician')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->operator), [
                'name' => $this->operator->name,
                'email' => $this->operator->email,
                'role_id' => $technicianRole->id,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame($technicianRole->id, $this->operator->fresh()->role_id);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user.role_changed',
            'subject_id' => $this->operator->id,
            'user_id' => $this->admin->id,
        ]);
    }
}
