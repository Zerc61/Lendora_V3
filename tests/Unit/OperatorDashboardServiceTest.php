<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\OperatorDashboardService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDashboardServiceTest extends TestCase
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

    public function test_it_builds_operator_kpis_and_today_queue(): void
    {
        $asset = $this->asset('LDR-CAM-8001', 'Lemari A');
        $this->asset('LDR-CAM-8002', 'Lemari B', AssetStatus::Borrowed);

        $this->booking(BookingStatus::Pending, $asset, now());
        $this->booking(BookingStatus::Approved, $asset, now(), now()->toDateString());
        $this->booking(BookingStatus::Active, $asset, now(), now()->subDays(3)->toDateString(), now()->toDateString());
        $this->booking(BookingStatus::Active, $asset, now(), now()->subDays(5)->toDateString(), now()->subDay()->toDateString());

        $dashboard = app(OperatorDashboardService::class)->for($this->operator);

        $this->assertSame(1, $dashboard['kpis']['pending']);
        $this->assertSame(1, $dashboard['kpis']['pickup_today']);
        $this->assertSame(1, $dashboard['kpis']['due_today']);
        $this->assertSame(1, $dashboard['kpis']['late']);
        $this->assertSame(4, $dashboard['todayQueue']->count());
        $this->assertSame('approval', $dashboard['todayQueue']->first()['tab']);
        $this->assertArrayHasKey('label', $dashboard['shift']);
        $this->assertArrayHasKey('lastShiftNote', $dashboard);
    }

    public function test_shelf_summary_groups_assets_by_location(): void
    {
        $this->asset('LDR-CAM-8010', 'Lemari A');
        $this->asset('LDR-CAM-8011', 'Lemari A', AssetStatus::Borrowed);
        $this->asset('LDR-CAM-8012', 'Lemari B', AssetStatus::Maintenance);

        $dashboard = app(OperatorDashboardService::class)->for($this->operator);
        $shelf = $dashboard['shelfSummary']->keyBy('location');

        $this->assertSame(2, $shelf['Lemari A']['total']);
        $this->assertSame(1, $shelf['Lemari A']['available']);
        $this->assertSame(1, $shelf['Lemari A']['borrowed']);
        $this->assertSame(1, $shelf['Lemari B']['maintenance']);
    }

    private function asset(string $code, string $location, AssetStatus $status = AssetStatus::Available): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Kamera Uji',
            'location' => $location,
            'status' => $status,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function booking(
        BookingStatus $status,
        Asset $asset,
        \Carbon\CarbonInterface $start,
        ?string $startDate = null,
        ?string $dueDate = null,
    ): Booking {
        $booking = Booking::create([
            'code' => 'LDR-BK-'.fake()->unique()->numerify('8###'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji dashboard',
            'requested_start_date' => $startDate ?? $start->toDateString(),
            'requested_end_date' => $dueDate ?? $start->copy()->addDays(2)->toDateString(),
            'due_at' => $dueDate !== null ? $dueDate.' 16:00:00' : null,
        ]);

        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        return $booking;
    }
}
