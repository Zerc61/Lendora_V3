<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Inspection;
use App\Models\Organization;
use App\Models\User;
use App\Services\KioskService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KioskServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private KioskService $kiosk;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->kiosk = app(KioskService::class);
    }

    public function test_lookup_finds_booking_by_code_nim_asset_and_return_token(): void
    {
        $booking = $this->booking(BookingStatus::Approved);

        $byCode = $this->kiosk->lookup($booking->code, $this->operator);
        $this->assertSame($booking->id, $byCode['booking']->id);
        $this->assertSame(HandoverType::Pickup, $byCode['type']);

        $this->borrower->update(['nim' => '2205346089']);

        $byNim = $this->kiosk->lookup('2205346089', $this->operator);        $this->assertSame($booking->id, $byNim['booking']->id);

        $asset = $booking->items()->firstOrFail()->asset;
        $byAsset = $this->kiosk->lookup($asset->code, $this->operator);
        $this->assertSame($booking->id, $byAsset['booking']->id);

        $booking->update(['return_token' => 'token-uji-123']);
        $byToken = $this->kiosk->lookup('token-uji-123', $this->operator);
        $this->assertSame($booking->id, $byToken['booking']->id);
    }

    public function test_lookup_unknown_code_raises_validation_error(): void
    {
        $this->expectException(ValidationException::class);

        $this->kiosk->lookup('TIDAK-ADA', $this->operator);
    }

    public function test_preflight_blocks_pickup_when_booking_is_not_approved(): void
    {
        $booking = $this->booking(BookingStatus::Pending);

        $result = $this->kiosk->preflight($booking, HandoverType::Pickup);

        $this->assertFalse($result['allowed']);
        $this->assertNotNull($result['reason']);
    }

    public function test_process_pickup_activates_booking_and_borrows_asset(): void
    {
        $booking = $this->booking(BookingStatus::Approved);
        $item = $booking->items()->firstOrFail();

        $processed = $this->kiosk->process(
            $booking,
            $this->operator,
            HandoverType::Pickup,
            [$item->id => 'good'],
            ['shift_label' => 'Pagi'],
        );

        $this->assertSame(BookingStatus::Active, $processed->status);
        $this->assertSame(AssetStatus::Borrowed, $item->asset->fresh()->status);
        $this->assertDatabaseHas('handovers', [
            'booking_id' => $booking->id,
            'type' => 'pickup',
            'shift_label' => 'Pagi',
        ]);
    }

    public function test_process_return_marks_booking_returned_and_runs_inspection(): void
    {
        $booking = $this->booking(BookingStatus::Active, AssetStatus::Borrowed);
        $item = $booking->items()->firstOrFail();

        $processed = $this->kiosk->process(
            $booking,
            $this->operator,
            HandoverType::Return,
            [$item->id => 'good'],
            ['inspect' => true],
        );

        $this->assertSame(BookingStatus::Returned, $processed->status);
        $this->assertSame(AssetStatus::Available, $item->asset->fresh()->status);
        $this->assertSame(1, Inspection::where('booking_item_id', $item->id)->count());
    }

    private function booking(
        BookingStatus $status,
        AssetStatus $assetStatus = AssetStatus::Reserved,
    ): Booking {
        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless-'.uniqid(),
        ]);

        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-'.random_int(1000, 9999),
            'name' => 'Kamera Uji',
            'status' => $assetStatus->value,
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-'.random_int(1000, 9999),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status->value,
            'purpose' => 'Uji kiosk',
            'requested_start_date' => now()->toDateString(),
            'requested_end_date' => now()->addDays(2)->toDateString(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'asset_id' => $asset->id,
            'status' => $status === BookingStatus::Active ? 'picked_up' : 'approved',
        ]);

        return $booking->fresh(['items.asset']);
    }
}
