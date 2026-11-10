<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\MaintenancePart;
use App\Models\Organization;
use App\Models\User;
use App\Services\SemesterReportService;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterReportServiceTest extends TestCase
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
            'name' => 'Kamera Cinema',
            'slug' => 'kamera-cinema',
        ]);
    }

    public function test_semester_metrics_cover_utilization_rejection_and_maintenance_cost(): void
    {
        $asset = $this->asset('LDR-CAM-0001');
        $this->asset('LDR-CAM-0002');

        $this->booking(BookingStatus::Approved, '2026-09-01', 10);
        $this->booking(BookingStatus::Rejected, '2026-09-15', 2);
        $this->booking(BookingStatus::Pending, '2026-09-20', 2);

        $maintenance = Maintenance::create([
            'asset_id' => $asset->id,
            'reported_by' => $this->prodi->id,
            'status' => MaintenanceStatus::Done,
            'priority' => 'normal',
            'description' => 'Servis sensor',
            'cost' => 100_000,
            'labor_cost' => 50_000,
        ]);

        MaintenancePart::create([
            'maintenance_id' => $maintenance->id,
            'name' => 'Filter',
            'quantity' => 2,
            'unit_cost' => 25_000,
        ]);

        $report = app(SemesterReportService::class)->for($this->prodi);

        $this->assertSame(120, $report['kpis']['demand_hours']);
        $this->assertSame(1, $report['kpis']['rejected']);
        $this->assertSame(33.3, $report['kpis']['rejection_rate']);
        $this->assertSame(1, $report['kpis']['incidents']);
        $this->assertSame(200_000, $report['kpis']['maintenance_cost']);
        $this->assertSame(1, $report['kpis']['bottlenecks']);
        $this->assertSame(10_000_000, $report['rab_total']);

        $proposal = $report['procurement']->first();

        $this->assertSame(1, $proposal['extra_units']);
        $this->assertSame(10_000_000, $proposal['unit_price']);
        $this->assertSame(10_000_000, $proposal['subtotal']);
        $this->assertSame('urgent', $proposal['priority']);

        $row = $report['rows']->first();

        $this->assertSame('Kamera Cinema', $row['category']->name);
        $this->assertSame(2, $row['units']);
        $this->assertSame(120, $row['demand_hours']);
        $this->assertSame(3, $row['requests']);
        $this->assertSame(1, $row['rejected']);
        $this->assertSame(33.3, $row['rejection_rate']);
        $this->assertSame('kritis', $row['status']);
    }

    public function test_category_without_rejection_or_high_utilization_is_balanced(): void
    {
        $this->asset('LDR-CAM-0001');
        $this->booking(BookingStatus::Approved, '2026-09-01', 2);

        $row = app(SemesterReportService::class)->for($this->prodi)['rows']->first();

        $this->assertSame('seimbang', $row['status']);
        $this->assertSame(0.0, $row['rejection_rate']);
    }

    public function test_document_identity_uses_organization_and_semester(): void
    {
        $semester = app(SemesterReportService::class)->for($this->prodi)['semester'];

        $this->assertSame('GANJIL', $semester['term']);
        $this->assertSame('SK-LAB-LDR/2026/SMT-GANJIL', $semester['document']);
    }

    private function asset(string $code): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Kamera '.$code,
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => 10_000_000,
        ]);
    }

    private function booking(BookingStatus $status, string $start, int $days): Booking
    {
        $asset = Asset::where('category_id', $this->category->id)->firstOrFail();
        $start = Carbon::parse($start);

        $booking = Booking::create([
            'code' => 'LDR-BK-SMT-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji laporan semester',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays($days),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'pending',
        ]);

        return $booking;
    }
}
