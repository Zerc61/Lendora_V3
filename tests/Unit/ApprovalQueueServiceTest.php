<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\ApprovalQueueService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalQueueServiceTest extends TestCase
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
            'name' => 'Lensa',
            'slug' => 'lensa',
        ]);
    }

    public function test_queue_marks_conflicts_and_suggests_alternatives(): void
    {
        $booked = $this->asset('LDR-LNS-8001');
        $alternative = $this->asset('LDR-LNS-8002');

        $start = Carbon::parse('2026-12-10');
        $end = $start->copy()->addDays(3);

        $this->booking($booked, BookingStatus::Approved, $start, $end);
        $pending = $this->booking($booked, BookingStatus::Pending, $start, $end);

        $queue = app(ApprovalQueueService::class)->for($this->operator);
        $card = $queue['cards']->firstWhere('booking.id', $pending->id);

        $this->assertSame(1, $queue['kpis']['waiting']);
        $this->assertSame(1, $queue['kpis']['conflicts']);
        $this->assertTrue($card['has_conflict']);
        $this->assertNotNull($card['conflict']);
        $this->assertSame($alternative->id, $card['alternatives'][0]['asset_id']);
        $this->assertTrue($card['can_decide']);
    }

    public function test_queue_exposes_block_reason_for_operator_value_limit(): void
    {
        $expensive = $this->asset('LDR-LNS-8010', 6_000_000);
        $this->booking($expensive, BookingStatus::Pending, Carbon::parse('2026-12-10'), Carbon::parse('2026-12-12'));

        $queue = app(ApprovalQueueService::class)->for($this->operator);
        $card = $queue['cards']->first();

        $this->assertFalse($card['can_decide']);
        $this->assertNotNull($card['block_reason']);
    }

    private function asset(string $code, int $price = 1_000_000): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Lensa Uji',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => $price,
        ]);
    }

    private function booking(Asset $asset, BookingStatus $status, Carbon $start, Carbon $end): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-'.fake()->unique()->numerify('8###'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji antrean',
            'requested_start_date' => $start,
            'requested_end_date' => $end,
        ]);

        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        return $booking;
    }
}
