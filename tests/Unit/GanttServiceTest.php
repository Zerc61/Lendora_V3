<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\GanttService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GanttServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Tripod',
            'slug' => 'tripod',
        ]);
    }

    public function test_it_builds_rows_bars_and_conflict_flags(): void
    {
        $asset = $this->asset('LDR-TRP-8001');
        $start = Carbon::parse('2026-12-01');

        $this->booking($asset, BookingStatus::Approved, $start->copy()->addDays(2), $start->copy()->addDays(6));
        $this->booking($asset, BookingStatus::Pending, $start->copy()->addDays(4), $start->copy()->addDays(8));

        $gantt = app(GanttService::class)->build($this->organization->id, $start, 14);

        $this->assertSame(14, $gantt['days']);
        $this->assertSame($start->toDateString(), $gantt['start']->toDateString());
        $this->assertCount(1, $gantt['rows']);
        $this->assertCount(2, $gantt['rows'][0]['bars']);
        $this->assertTrue($gantt['rows'][0]['conflict']);
        $this->assertSame(1, $gantt['kpis']['conflicts']);
        $this->assertSame('booking', $gantt['rows'][0]['bars'][0]['type']);
        $this->assertSame(2, $gantt['rows'][0]['bars'][0]['offset']);
    }

    public function test_maintenance_status_creates_a_block_bar(): void
    {
        $asset = $this->asset('LDR-TRP-8010', AssetStatus::Maintenance);

        $gantt = app(GanttService::class)->build($this->organization->id, Carbon::parse('2026-12-01'), 7);

        $this->assertSame('block', $gantt['rows'][0]['bars'][0]['type']);
        $this->assertSame('maintenance', $gantt['rows'][0]['bars'][0]['status']);
    }

    private function asset(string $code, AssetStatus $status = AssetStatus::Available): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Tripod Uji',
            'status' => $status,
            'condition_status' => 'good',
            'purchase_price' => 500_000,
        ]);
    }

    private function booking(Asset $asset, BookingStatus $status, Carbon $start, Carbon $end): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-'.fake()->unique()->numerify('8###'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji gantt',
            'requested_start_date' => $start,
            'requested_end_date' => $end,
        ]);

        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        return $booking;
    }
}
