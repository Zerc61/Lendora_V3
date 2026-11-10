<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDashboardTest extends TestCase
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

    public function test_operator_dashboard_exposes_operational_panels(): void
    {
        $asset = $this->asset('LDR-CAM-8100', 'Lemari A');
        $booking = $this->booking($asset, BookingStatus::Pending, Carbon::parse(today()));

        $response = $this->actingAs($this->operator)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $dashboard = $response->viewData('operatorDashboard');

        $this->assertSame(1, $dashboard['kpis']['pending']);
        $this->assertArrayHasKey('pickup_today', $dashboard['kpis']);
        $this->assertArrayHasKey('late', $dashboard['kpis']);
        $this->assertSame($booking->code, $dashboard['todayQueue']->first()['booking']->code);
        $this->assertSame('Lemari A', $dashboard['shelfSummary']->first()['location']);
        $this->assertArrayHasKey('label', $dashboard['shift']);
    }

    public function test_operator_dashboard_renders_w1_shell(): void
    {
        $asset = $this->asset('LDR-CAM-8103', 'Lemari B');
        $booking = $this->booking($asset, BookingStatus::Pending, now()->addDay());

        $this->actingAs($this->operator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-op-tabs', false)
            ->assertSee('data-op-tab="approval"', false)
            ->assertSee('data-op-row="approval"', false)
            ->assertSee('shift-swap-modal', false)
            ->assertSee('has-notice', false)
            ->assertSee(__('ui.sections.operator_counter'))
            ->assertSee(__('ui.sections.inventory_schedule'))
            ->assertSee($booking->code);
    }

    public function test_approval_queue_and_report_print_pages_render(): void
    {
        $asset = $this->asset('LDR-CAM-8101', 'Lemari A');
        $booking = $this->booking($asset, BookingStatus::Pending, now()->addDay());

        $this->actingAs($this->operator)
            ->get(route('admin.bookings.approval'))
            ->assertOk()
            ->assertSee($booking->code)
            ->assertSee(__('ui.approval.title'));

        $this->actingAs($this->operator)
            ->get(route('admin.reports.print'))
            ->assertOk()
            ->assertSee(__('ui.report.print_title'));
    }

    public function test_operator_dashboard_blocks_requests_above_operator_authority(): void
    {
        $asset = $this->asset('LDR-CAM-8104', 'Lemari A');
        $booking = Booking::create([
            'code' => 'LDR-BK-2026-8104',
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Durasi panjang',
            'requested_start_date' => now()->addDay(),
            'requested_end_date' => now()->addDays(10),
        ]);
        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        $response = $this->actingAs($this->operator)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $dashboard = $response->viewData('operatorDashboard');
        $item = $dashboard['todayQueue']->first(fn (array $row) => $row['booking']->is($booking));

        $this->assertNotNull($item['block_reason']);
        $response->assertSee($item['block_reason']);
        $response->assertSee('ldr-op-block', false);
    }

    public function test_asset_export_returns_csv(): void
    {
        $asset = $this->asset('LDR-CAM-8102', 'Lemari A');

        $response = $this->actingAs($this->operator)
            ->get(route('admin.assets.export'))
            ->assertOk();

        $this->assertStringContainsString('Kode,Nama,Kategori', $response->streamedContent());
        $this->assertStringContainsString($asset->code, $response->streamedContent());
    }

    private function asset(string $code, string $location): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => $code,
            'name' => 'Kamera Uji',
            'location' => $location,
            'status' => 'available',
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);
    }

    private function booking(Asset $asset, BookingStatus $status, Carbon $start): Booking
    {
        $booking = Booking::create([
            'code' => 'LDR-BK-'.fake()->unique()->numerify('8###'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => $status,
            'purpose' => 'Uji dashboard',
            'requested_start_date' => $start,
            'requested_end_date' => $start->copy()->addDays(2),
        ]);

        $booking->items()->create(['asset_id' => $asset->id, 'status' => 'pending']);

        return $booking;
    }
}
