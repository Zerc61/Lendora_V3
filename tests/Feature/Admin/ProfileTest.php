<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingReceivedNotification;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);
    }

    public function test_staff_can_update_own_profile_and_notification_preferences(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $this->actingAs($operator)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Preferensi notifikasi')
            ->assertSee('Informasi akun');

        $this->actingAs($operator)
            ->put(route('admin.profile.update'), [
                'name' => 'Operator Baru',
                'phone' => '081200000000',
                'notifications' => ['asset_damage' => '0'],
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $operator->refresh();

        $this->assertSame('Operator Baru', $operator->name);
        $this->assertSame('081200000000', $operator->phone);
        $this->assertFalse($operator->wantsNotification('asset_damage'));
        $this->assertTrue($operator->wantsNotification('booking_received'));
    }

    public function test_borrower_cannot_open_admin_profile(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)
            ->get(route('admin.profile.edit'))
            ->assertForbidden();
    }

    public function test_disabled_notification_is_not_delivered(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $borrower->update(['notification_preferences' => ['booking_received' => false]]);

        $notification = new BookingReceivedNotification(new Booking);

        $this->assertSame([], $notification->via($borrower));
        $this->assertSame(['database'], $notification->via(User::where('email', 'borrower2@lendora.test')->firstOrFail()));
    }
}
