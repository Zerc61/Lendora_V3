<?php

namespace Tests\Feature\Borrower;

use App\Models\User;
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

    public function test_borrower_sees_and_updates_notification_preferences(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)
            ->get(route('borrower.profile.edit'))
            ->assertOk()
            ->assertSee('Preferensi notifikasi');

        $this->actingAs($borrower)
            ->put(route('borrower.profile.update'), [
                'name' => $borrower->name,
                'notifications' => [
                    'booking_received' => '1',
                    'booking_decision' => '1',
                    'loan_pickup' => '0',
                    'loan_due_reminder' => '1',
                ],
            ])
            ->assertRedirect(route('borrower.profile.edit'));

        $borrower->refresh();

        $this->assertTrue($borrower->wantsNotification('booking_received'));
        $this->assertFalse($borrower->wantsNotification('loan_pickup'));
        $this->assertTrue($borrower->wantsNotification('loan_due_reminder'));
        $this->assertTrue($borrower->wantsNotification('asset_damage'));
    }

    public function test_unknown_preference_keys_are_not_stored(): void
    {
        $borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();

        $this->actingAs($borrower)->put(route('borrower.profile.update'), [
            'name' => $borrower->name,
            'notifications' => ['injected_key' => '1'],
        ]);

        $this->assertArrayNotHasKey('injected_key', $borrower->refresh()->notification_preferences);
    }
}
