<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\LoanPickupNotification;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $operator;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_pickup_activates_booking_and_marks_assets_borrowed(): void
    {
        Notification::fake();

        $booking = $this->booking(BookingStatus::Approved, AssetStatus::Reserved, BookingItemStatus::Approved);
        $item = $booking->items->first();
        $asset = $item->asset;

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.handover.store', $booking), [
                'type' => HandoverType::Pickup->value,
                'conditions' => [$item->id => 'good'],
                'notes' => 'Diambil tepat waktu.',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingStatus::Active, $booking->status);
        $this->assertNotNull($booking->pickup_at);
        $this->assertSame(BookingItemStatus::PickedUp, $item->fresh()->status);
        $this->assertSame('good', $item->fresh()->condition_out);
        $this->assertSame(AssetStatus::Borrowed, $asset->fresh()->status);
        $this->assertDatabaseHas('handovers', [
            'booking_id' => $booking->id,
            'type' => HandoverType::Pickup->value,
            'performed_by' => $this->operator->id,
        ]);
        Notification::assertSentTo($this->borrower, LoanPickupNotification::class);
    }

    public function test_index_lists_only_bookings_waiting_for_handover(): void
    {
        $approved = $this->booking(BookingStatus::Approved, AssetStatus::Reserved, BookingItemStatus::Approved);
        $active = $this->booking(BookingStatus::Active, AssetStatus::Borrowed, BookingItemStatus::PickedUp);
        $pending = $this->booking(BookingStatus::Pending, AssetStatus::Available, BookingItemStatus::Pending);

        $this->actingAs($this->operator)
            ->get(route('admin.handovers.index'))
            ->assertOk()
            ->assertSee($approved->code)
            ->assertSee($active->code)
            ->assertDontSee($pending->code);
    }

    public function test_handover_form_renders_items_and_conditions(): void
    {
        $booking = $this->booking(BookingStatus::Approved, AssetStatus::Reserved, BookingItemStatus::Approved);

        $this->actingAs($this->operator)
            ->get(route('admin.bookings.handover', ['booking' => $booking, 'type' => 'pickup']))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee('Kamera Uji')
            ->assertSee('Kondisi unit');
    }

    public function test_borrower_cannot_open_handover_index(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('admin.handovers.index'))
            ->assertForbidden();
    }

    public function test_handover_index_filters_by_type_and_search(): void
    {
        $approved = $this->booking(BookingStatus::Approved, AssetStatus::Reserved, BookingItemStatus::Approved);
        $active = $this->booking(BookingStatus::Active, AssetStatus::Borrowed, BookingItemStatus::PickedUp);

        $this->actingAs($this->operator)
            ->get(route('admin.handovers.index', ['type' => 'pickup']))
            ->assertOk()
            ->assertSee($approved->code)
            ->assertDontSee($active->code);

        $this->actingAs($this->operator)
            ->get(route('admin.handovers.index', ['type' => 'return']))
            ->assertOk()
            ->assertSee($active->code)
            ->assertDontSee($approved->code);

        $this->actingAs($this->operator)
            ->get(route('admin.handovers.index', ['search' => $approved->code]))
            ->assertOk()
            ->assertSee($approved->code)
            ->assertDontSee($active->code);
    }

    public function test_return_marks_booking_returned_but_asset_waits_for_inspection(): void    {
        $booking = $this->booking(BookingStatus::Active, AssetStatus::Borrowed, BookingItemStatus::PickedUp);
        $item = $booking->items->first();
        $asset = $item->asset;

        $this->actingAs($this->operator)
            ->post(route('admin.bookings.handover.store', $booking), [
                'type' => HandoverType::Return->value,
                'conditions' => [$item->id => 'good'],
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();

        $this->assertSame(BookingStatus::Returned, $booking->status);
        $this->assertNotNull($booking->returned_at);
        $this->assertSame(BookingItemStatus::Returned, $item->fresh()->status);
        $this->assertSame('good', $item->fresh()->condition_in);
        $this->assertSame(AssetStatus::Borrowed, $asset->fresh()->status);
        $this->assertDatabaseHas('handovers', [
            'booking_id' => $booking->id,
            'type' => HandoverType::Return->value,
        ]);
    }

    private function booking(BookingStatus $status, AssetStatus $assetStatus, BookingItemStatus $itemStatus): Booking
    {
        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => $assetStatus,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji serah terima',
            'requested_start_date' => Carbon::parse('2026-12-01'),
            'requested_end_date' => Carbon::parse('2026-12-03'),
            'pickup_at' => $status === BookingStatus::Active ? now() : null,
            'due_at' => $status === BookingStatus::Active ? now()->addDay() : null,
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => $itemStatus,
        ]);

        return $booking->load('items.asset');
    }
}
