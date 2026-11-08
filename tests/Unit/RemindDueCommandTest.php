<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\LoanDueReminderNotification;
use App\Services\SettingService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RemindDueCommandTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower1;

    private User $borrower2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class, SettingSeeder::class]);

        $this->borrower1 = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->borrower2 = User::where('email', 'borrower2@lendora.test')->firstOrFail();
        $this->organization = $this->borrower1->organization;
    }

    public function test_only_active_bookings_due_tomorrow_are_notified(): void
    {
        Notification::fake();

        $dueTomorrow = $this->booking($this->borrower1, BookingStatus::Active, now()->addDay());
        $this->booking($this->borrower2, BookingStatus::Active, now()->addDays(2));
        $this->booking($this->borrower2, BookingStatus::Pending, now()->addDay());

        $this->artisan('loans:remind-due')->assertSuccessful();

        Notification::assertSentTo(
            $this->borrower1,
            LoanDueReminderNotification::class,
            fn (LoanDueReminderNotification $notification) => $notification->booking->code === $dueTomorrow->code,
        );
        Notification::assertNotSentTo($this->borrower2, LoanDueReminderNotification::class);
        Notification::assertCount(1);
    }

    public function test_disabled_toggle_sends_no_reminder(): void
    {
        Notification::fake();

        app(SettingService::class)->set('return_reminder_enabled', '0', $this->organization->id);

        $this->booking($this->borrower1, BookingStatus::Active, now()->addDay());

        $this->artisan('loans:remind-due')->assertSuccessful();

        Notification::assertNothingSent();
    }

    private function booking(User $borrower, BookingStatus $status, $dueAt): Booking
    {
        return Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'status' => $status,
            'purpose' => 'Uji pengingat',
            'requested_start_date' => now()->subDays(2)->toDateString(),
            'requested_end_date' => $dueAt->copy()->toDateString(),
            'due_at' => $dueAt,
        ]);
    }
}
