<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_staff_sees_own_notifications_and_can_mark_them_read(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $notificationId = (string) Str::uuid();

        $operator->notifications()->create([
            'id' => $notificationId,
            'type' => 'test',
            'data' => ['message' => 'Unit kamera perlu diperiksa.'],
        ]);

        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Notifikasi milik admin.'],
        ]);

        $this->actingAs($operator)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Unit kamera perlu diperiksa.')
            ->assertDontSee('Notifikasi milik admin.');

        $this->actingAs($operator)
            ->post(route('admin.notifications.read', $notificationId))
            ->assertRedirect();

        $this->assertNotNull($operator->notifications()->whereKey($notificationId)->first()?->read_at);
    }

    public function test_borrower_cannot_open_staff_notifications(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();
    }
}
