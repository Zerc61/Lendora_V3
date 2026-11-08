<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\BookingDecisionNotification;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApprovalQueueTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $operator;

    private User $admin;

    private User $prodi;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_operator_approves_routine_request_and_notifies_borrower(): void
    {
        Notification::fake();

        $asset = $this->asset();
        $booking = $this->pendingBooking($asset);

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingStatus::Approved, $booking->status);
        $this->assertSame($this->operator->id, $booking->approved_by);
        $this->assertSame(AssetStatus::Reserved, $asset->fresh()->status);
        Notification::assertSentTo($this->borrower, BookingDecisionNotification::class);
    }

    public function test_operator_is_blocked_above_operator_approval_limit(): void
    {
        $booking = $this->pendingBooking($this->asset(purchasePrice: 6_000_000));

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.approve', $booking))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_reject_without_reason_fails_validation(): void
    {
        $booking = $this->pendingBooking($this->asset());

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.reject', $booking), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
    }

    public function test_operator_rejects_with_reason(): void
    {
        $booking = $this->pendingBooking($this->asset());

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.reject', $booking), [
                'rejection_reason' => 'Jadwal bertabrakan dengan kegiatan lain.',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingStatus::Rejected, $booking->status);
        $this->assertSame('Jadwal bertabrakan dengan kegiatan lain.', $booking->rejection_reason);
    }

    public function test_admin_prodi_approves_long_duration_but_not_short_requests(): void
    {
        $short = $this->pendingBooking($this->asset(), duration: 3);
        $long = $this->pendingBooking($this->asset(), duration: 10);

        $this->actingAs($this->prodi)
            ->post(route('admin.bookings.approve', $short))
            ->assertForbidden();

        $this->actingAs($this->prodi)
            ->post(route('admin.bookings.approve', $long))
            ->assertRedirect(route('admin.bookings.show', $long));

        $this->assertSame(BookingStatus::Approved, $long->fresh()->status);
    }

    public function test_organization_admin_approves_high_value_request(): void
    {
        $booking = $this->pendingBooking($this->asset(purchasePrice: 9_000_000));

        $this->actingAs($this->admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertSame(BookingStatus::Approved, $booking->fresh()->status);
    }

    public function test_queue_defaults_to_pending_and_shows_counts(): void
    {
        $pending = $this->pendingBooking($this->asset());
        $approved = $this->pendingBooking($this->asset());
        $approved->update(['status' => BookingStatus::Approved]);

        $this->actingAs($this->operator)
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee($pending->code)
            ->assertDontSee($approved->code);
    }

    public function test_calendar_lists_booked_ranges_for_unit(): void
    {
        $asset = $this->asset();
        $booking = $this->pendingBooking($asset);
        $booking->update(['status' => BookingStatus::Approved]);

        $this->actingAs($this->operator)
            ->get(route('admin.bookings.calendar', ['month' => '2026-12']))
            ->assertOk()
            ->assertSee($asset->code)
            ->assertSee($booking->code);
    }

    private function asset(int $purchasePrice = 1_000_000): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => $purchasePrice,
        ]);
    }

    private function pendingBooking(Asset $asset, int $duration = 3): Booking
    {
        $start = Carbon::parse('2026-12-01');

        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji antrean',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays($duration),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        return $booking;
    }
}
