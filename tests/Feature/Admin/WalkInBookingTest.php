<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\BookingAlternativeNotification;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WalkInBookingTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_operator_creates_walk_in_and_auto_approves(): void
    {
        $asset = $this->asset('LDR-CAM-8200');

        $this->actingAs($this->operator)
            ->get(route('admin.bookings.walk-in'))
            ->assertOk()
            ->assertSee($this->borrower->name)
            ->assertSee('ldr-walkin-form', false)
            ->assertSee('ldr-walkin-unit', false);

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.walk-in.store'), [
                'borrower_id' => $this->borrower->id,
                'asset_ids' => [$asset->id],
                'requested_start_date' => now()->addDay()->toDateString(),
                'requested_end_date' => now()->addDays(3)->toDateString(),
                'purpose' => 'Praktikum foto dari loket',
            ])
            ->assertRedirect();

        $booking = Booking::firstOrFail();

        $this->assertSame(BookingStatus::Approved, $booking->status);
        $this->assertSame(AssetStatus::Reserved, $asset->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'booking.walk_in']);
    }

    public function test_suggest_alternative_notifies_borrower_on_conflict(): void
    {
        Notification::fake();

        $booked = $this->asset('LDR-CAM-8201');
        $alternative = $this->asset('LDR-CAM-8202');
        $start = Carbon::parse(now()->addDays(2)->toDateString());

        $this->booking($booked, BookingStatus::Approved, $start, $start->copy()->addDays(4));
        $pending = $this->booking($booked, BookingStatus::Pending, $start->copy()->addDay(), $start->copy()->addDays(5));

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.suggest', $pending))
            ->assertRedirect();

        Notification::assertSentTo($this->borrower, BookingAlternativeNotification::class);
        $this->assertDatabaseHas('activity_logs', ['action' => 'booking.suggested_alternative']);

        $this->assertDatabaseHas('assets', ['id' => $alternative->id, 'status' => AssetStatus::Available->value]);
    }

    public function test_operator_reassigns_item_to_available_unit(): void
    {
        $original = $this->asset('LDR-CAM-8203');
        $replacement = $this->asset('LDR-CAM-8204');
        $start = Carbon::parse(now()->addDays(2)->toDateString());

        $booking = $this->booking($original, BookingStatus::Approved, $start, $start->copy()->addDays(3));
        $original->update(['status' => AssetStatus::Reserved]);
        $item = $booking->items()->firstOrFail();

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.reassign', $booking), [
                'booking_item_id' => $item->id,
                'asset_id' => $replacement->id,
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertSame($replacement->id, $item->fresh()->asset_id);
        $this->assertSame(AssetStatus::Available, $original->fresh()->status);
        $this->assertSame(AssetStatus::Reserved, $replacement->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'booking.reassigned']);
    }

    private function asset(string $code): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function booking(Asset $asset, BookingStatus $status, Carbon $start, Carbon $end): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-'.fake()->unique()->numerify('8###'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji walk-in',
            'requested_start_date' => $start,
            'requested_end_date' => $end,
        ]);

        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        return $booking;
    }
}
