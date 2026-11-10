<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
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

class ProdiDashboardTest extends TestCase
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

    public function test_prodi_dashboard_renders_special_queue_with_value_and_identity(): void
    {
        $booking = $this->booking($this->asset(6_500_000), 10);
        $this->borrower->update(['nim' => '2207410001']);

        $this->actingAs($this->prodi)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('ui.prodi.title'))
            ->assertSee(__('ui.prodi.queue_title'))
            ->assertSee($booking->code)
            ->assertSee('2207410001')
            ->assertSee('Rp 6.500.000')
            ->assertSee(__('ui.prodi.approve'))
            ->assertSee(__('ui.prodi.rule_title'))
            ->assertSee(__('ui.prodi.category_title'))
            ->assertSee('ldr-sidebar__badge', false);
    }

    public function test_prodi_dashboard_shows_empty_state_without_special_requests(): void
    {
        $this->booking($this->asset(), 3);

        $this->actingAs($this->prodi)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('ui.prodi.queue_empty'))
            ->assertDontSee(__('ui.prodi.approve'));
    }

    public function test_prodi_detail_shows_verification_panel_for_special_request(): void
    {
        $booking = $this->booking($this->asset(9_000_000), 10);
        $this->borrower->update([
            'dpa_name' => 'Bpk. Harjo',
            'academic_verified_at' => now(),
        ]);

        $this->actingAs($this->prodi)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee(__('ui.prodi.identity_title'))
            ->assertSee(__('ui.prodi.escalation_title'))
            ->assertSee(__('ui.prodi.escalation_duration', ['days' => 10, 'max' => 7]))
            ->assertSee('Bpk. Harjo')
            ->assertSee(__('ui.prodi.verified'));
    }

    public function test_prodi_detail_hides_verification_panel_for_short_request(): void
    {
        $booking = $this->booking($this->asset(), 3);

        $this->actingAs($this->prodi)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertDontSee(__('ui.prodi.identity_title'))
            ->assertDontSee(__('ui.prodi.approve'));
    }

    private function asset(int $price = 2_000_000): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('9###'),
            'name' => 'Sony FX3',
            'status' => AssetStatus::Available,
            'condition_status' => 'good',
            'purchase_price' => $price,
        ]);
    }

    private function booking(Asset $asset, int $days): Booking
    {
        $start = Carbon::parse('2026-12-01');

        $booking = Booking::create([
            'code' => 'LDR-BK-PRD-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Pending,
            'purpose' => 'Produksi film pendek',
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
