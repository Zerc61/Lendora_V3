<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\ProdiDashboardService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdiDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $prodi;

    private User $borrower;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_summary_matches_special_queue_and_detects_low_compliance(): void
    {
        $long = $this->booking(10);
        $this->booking(3);
        $this->borrower->update(['compliance_score' => 45]);

        $summary = app(ProdiDashboardService::class)->for($this->prodi);

        $this->assertSame(2, $summary['kpis']['pending']);
        $this->assertSame(1, $summary['kpis']['special']);
        $this->assertSame(1, $summary['kpis']['low_compliance']);
        $this->assertCount(1, $summary['queue']);
        $this->assertTrue($summary['queue']->first()['booking']->is($long));
        $this->assertSame(2_000_000, $summary['queue']->first()['value']);
        $this->assertSame('Kamera Mirrorless', $summary['categories']->first()['name']);
        $this->assertSame(1, $summary['categories']->first()['count']);
        $this->assertNotNull($summary['semester']['label']);
    }

    public function test_queue_ignores_past_requests(): void
    {
        $past = Carbon::parse('2026-01-01');

        $booking = Booking::create([
            'code' => 'LDR-BK-PAST-9001',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Permintaan lama',
            'requested_start_date' => $past,
            'requested_end_date' => $past->copy()->addDays(10),
        ]);
        $booking->items()->create([
            'asset_id' => $this->asset()->id,
            'status' => 'pending',
        ]);

        $summary = app(ProdiDashboardService::class)->for($this->prodi);

        $this->assertSame(0, $summary['kpis']['special']);
        $this->assertCount(0, $summary['queue']);
    }

    private function asset(): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('8###'),
            'name' => 'Sony FX3',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 2_000_000,
        ]);
    }

    private function booking(int $days): Booking
    {
        $start = Carbon::parse('2026-12-01');

        $booking = Booking::create([
            'code' => 'LDR-BK-UNIT-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Produksi film pendek',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays($days),
        ]);

        $booking->items()->create([
            'asset_id' => $this->asset()->id,
            'status' => 'pending',
        ]);

        return $booking;
    }
}
