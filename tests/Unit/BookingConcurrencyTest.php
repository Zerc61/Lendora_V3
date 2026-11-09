<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingConcurrencyTest extends TestCase
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

    public function test_only_one_of_two_overlapping_approvals_succeeds(): void
    {
        $asset = $this->asset();

        $first = $this->pendingBooking($asset, '2026-12-01', '2026-12-04');
        $second = $this->pendingBooking($asset, '2026-12-03', '2026-12-06');

        $approved = app(BookingService::class)->approve($first, $this->operator);

        $this->assertSame(BookingStatus::Approved, $approved->status);
        $this->assertSame(AssetStatus::Reserved, $asset->fresh()->status);

        try {
            app(BookingService::class)->approve($second, $this->operator);
            $this->fail('Second overlapping approval must fail.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString($first->code, $exception->errors()['assets'][0]);
        }

        $this->assertSame(BookingStatus::Pending, $second->fresh()->status);
        $this->assertSame(1, Booking::where('status', BookingStatus::Approved->value)->count());
    }

    private function asset(): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-0001',
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function pendingBooking(Asset $asset, string $start, string $end): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Uji konkurensi',
            'requested_start_date' => Carbon::parse($start),
            'requested_end_date' => Carbon::parse($end),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        return $booking->load('items.asset');
    }
}
