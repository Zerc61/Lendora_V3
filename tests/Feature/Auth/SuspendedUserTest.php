<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspendedUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_suspended_borrower_is_blocked_from_borrower_area(): void
    {
        $user = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $user->update(['status' => 'suspended']);

        $this->actingAs($user)->get('/borrower')->assertForbidden();
    }

    public function test_suspended_admin_is_blocked_from_admin_area(): void
    {
        $user = User::where('email', 'admin@lendora.test')->firstOrFail();
        $user->update(['status' => 'suspended']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
