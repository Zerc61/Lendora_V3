<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_available_asset_without_bookings_is_available_for_range(): void
    {
        $asset = new Asset(['status' => AssetStatus::Available]);

        $this->assertTrue(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-10'),
                Carbon::parse('2026-01-12'),
            ),
        );
    }

    public function test_non_available_asset_is_rejected(): void
    {
        $asset = new Asset(['status' => AssetStatus::Maintenance]);

        $this->assertFalse(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-10'),
                Carbon::parse('2026-01-12'),
            ),
        );
    }

    public function test_partial_overlap_is_a_conflict(): void
    {
        $asset = $this->asset();
        $this->booking($asset, '2026-01-05', '2026-01-10', BookingStatus::Approved);

        $this->assertFalse(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-08'),
                Carbon::parse('2026-01-12'),
            ),
        );
    }

    public function test_full_containment_is_a_conflict(): void
    {
        $asset = $this->asset();
        $this->booking($asset, '2026-01-01', '2026-01-20', BookingStatus::Approved);

        $this->assertFalse(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-05'),
                Carbon::parse('2026-01-10'),
            ),
        );
    }

    public function test_touching_ranges_are_not_a_conflict(): void
    {
        $asset = $this->asset();
        $this->booking($asset, '2026-01-05', '2026-01-10', BookingStatus::Approved);

        $this->assertTrue(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-10'),
                Carbon::parse('2026-01-12'),
            ),
        );
    }

    public function test_non_holding_statuses_are_ignored(): void
    {
        $asset = $this->asset();

        foreach ([BookingStatus::Pending, BookingStatus::Rejected, BookingStatus::Cancelled, BookingStatus::Returned] as $status) {
            $booking = $this->booking($asset, '2026-01-01', '2026-01-20', $status);

            $this->assertTrue(
                app(AvailabilityService::class)->isAvailableForRange(
                    $asset,
                    Carbon::parse('2026-01-05'),
                    Carbon::parse('2026-01-10'),
                ),
                "Status {$status->value} must not hold the asset.",
            );

            $booking->forceDelete();
        }
    }

    public function test_suggest_free_range_skips_the_conflict(): void
    {
        $asset = $this->asset();
        $this->booking($asset, '2026-01-10', '2026-01-15', BookingStatus::Approved);

        $suggestion = app(AvailabilityService::class)->suggestFreeRange(
            $asset,
            Carbon::parse('2026-01-10'),
            Carbon::parse('2026-01-12'),
        );

        $this->assertNotNull($suggestion);
        $this->assertSame('2026-01-15', $suggestion->toDateString());
    }

    public function test_soft_deleted_bookings_do_not_hold_the_asset(): void
    {
        $asset = $this->asset();
        $booking = $this->booking($asset, '2026-01-01', '2026-01-20', BookingStatus::Approved);
        $booking->delete();

        $this->assertTrue(
            app(AvailabilityService::class)->isAvailableForRange(
                $asset,
                Carbon::parse('2026-01-05'),
                Carbon::parse('2026-01-10'),
            ),
        );
    }

    private function asset(): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function booking(Asset $asset, string $start, string $end, BookingStatus $status): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji ketersediaan',
            'requested_start_date' => $start,
            'requested_end_date' => $end,
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'approved',
        ]);

        return $booking;
    }
}
