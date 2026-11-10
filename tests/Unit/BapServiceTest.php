<?php

namespace Tests\Unit;

use App\Enums\PenaltyStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\User;
use App\Services\BapService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BapServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Booking $booking;

    private BapService $bap;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->booking = $this->booking();
        $this->bap = app(BapService::class);
    }

    public function test_create_generates_number_and_totals_from_items(): void
    {
        $penalty = $this->bap->create($this->operator, $this->payload());

        $this->assertSame('LDR-BAP-'.now()->year.'-0001', $penalty->bap_number);
        $this->assertSame(PenaltyStatus::WaitingPayment, $penalty->status);
        $this->assertNull($penalty->issued_at);
        $this->assertSame(3_000_000, $penalty->repair_subtotal);
        $this->assertSame(300_000, $penalty->subsidy_amount);
        $this->assertSame(50_000, $penalty->admin_fee);
        $this->assertSame(2_750_000, $penalty->total_amount);
        $this->assertCount(2, $penalty->items);

        $second = $this->bap->create($this->operator, $this->payload());

        $this->assertSame('LDR-BAP-'.now()->year.'-0002', $second->bap_number);
    }

    public function test_issue_sets_due_date_and_suspends_borrower(): void
    {
        $this->borrower->update(['compliance_score' => 100]);
        $penalty = $this->bap->create($this->operator, $this->payload());

        $issued = $this->bap->issue($penalty, $this->operator);

        $this->assertNotNull($issued->issued_at);
        $this->assertSame($this->operator->id, $issued->issued_by);
        $this->assertSame('suspended', $this->borrower->fresh()->status);
        $this->assertSame(80, $this->borrower->fresh()->compliance_score);
        $this->assertTrue($issued->due_at->isFuture());
    }

    public function test_issue_twice_is_rejected(): void
    {
        $penalty = $this->bap->create($this->operator, $this->payload());
        $this->bap->issue($penalty, $this->operator);

        $this->expectException(ValidationException::class);

        $this->bap->issue($penalty->fresh(), $this->operator);
    }

    public function test_recalculate_uses_subsidy_and_admin_fee(): void
    {
        $penalty = $this->bap->create($this->operator, $this->payload());
        $penalty->update(['subsidy_percent' => 25, 'admin_fee' => 100_000]);

        $recalculated = $this->bap->recalculate($penalty);

        $this->assertSame(750_000, $recalculated->subsidy_amount);
        $this->assertSame(2_350_000, $recalculated->total_amount);
    }

    private function payload(): array
    {
        return [
            'organization_id' => $this->organization->id,
            'booking_id' => $this->booking->id,
            'asset_id' => $this->booking->items->first()->asset_id,
            'borrower_id' => $this->borrower->id,
            'incident_at' => now()->subDay()->toDateTimeString(),
            'inspector_name' => 'Budi Santoso',
            'damage_description' => 'Mount bengkok.',
            'case_category' => 'Kerusakan ringan',
            'chronology' => 'Unit terjatuh.',
            'payment_terms_days' => 7,
            'subsidy_percent' => 10,
            'admin_fee' => 50_000,
            'items' => [
                ['description' => 'Mount ring', 'part_code' => 'PART-MNT', 'quantity' => 2, 'unit_cost' => 1_000_000],
                ['description' => 'Jasa kalibrasi', 'quantity' => 1, 'unit_cost' => 1_000_000],
            ],
        ];
    }

    private function booking(): Booking
    {
        $category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);

        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => 'LDR-CAM-9002',
            'name' => 'Kamera Uji',
            'status' => 'maintenance',
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-2026-9002',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => 'returned',
            'purpose' => 'Uji BAP',
            'requested_start_date' => now()->subDays(5)->toDateString(),
            'requested_end_date' => now()->subDays(2)->toDateString(),
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'asset_id' => $asset->id,
            'status' => 'damaged',
        ]);

        return $booking->fresh(['items']);
    }
}
