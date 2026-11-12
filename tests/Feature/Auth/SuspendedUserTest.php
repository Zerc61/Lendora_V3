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

    public function test_suspended_user_cannot_log_in(): void
    {
        $user = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $user->update(['status' => 'suspended']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertSame(__('messages.suspended'), session('errors')->first('email'));
    }

    public function test_suspended_user_sees_logout_instead_of_home_link(): void
    {
        $user = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $user->update(['status' => 'suspended']);

        $response = $this->actingAs($user)->get('/borrower');

        $response->assertForbidden();
        $response->assertSee(__('messages.suspended'));
        $response->assertSee(route('logout', absolute: false), false);
        $response->assertDontSee(__('ui.error.back_home'));
    }
}
