<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Enums\InspectionResult;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\AssetDamageNotification;
use Carbon\Carbon;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InspectionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    private User $operator;

    private User $admin;

    private User $technician;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->technician = User::where('email', 'technician@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_needs_repair_creates_maintenance_and_notifies_staff(): void
    {
        Notification::fake();

        $asset = $this->borrowedAsset();

        $this->actingAs($this->technician)
            ->post(route('admin.inspections.store'), [
                'asset_id' => $asset->id,
                'result' => InspectionResult::NeedsRepair->value,
                'notes' => 'Lensa berdebu.',
            ])
            ->assertRedirect(route('admin.inspections.index'));

        $this->assertDatabaseHas('inspections', [
            'asset_id' => $asset->id,
            'inspector_id' => $this->technician->id,
            'result' => InspectionResult::NeedsRepair->value,
        ]);
        $this->assertDatabaseHas('maintenances', [
            'asset_id' => $asset->id,
            'status' => MaintenanceStatus::Scheduled->value,
        ]);
        $this->assertSame(AssetStatus::Maintenance, $asset->fresh()->status);

        Notification::assertSentTo($this->operator, AssetDamageNotification::class);
        Notification::assertSentTo($this->admin, AssetDamageNotification::class);
    }

    public function test_ok_result_returns_asset_to_available(): void
    {
        $asset = $this->borrowedAsset();

        $this->actingAs($this->operator)
            ->post(route('admin.inspections.store'), [
                'asset_id' => $asset->id,
                'result' => InspectionResult::Ok->value,
            ])
            ->assertRedirect(route('admin.inspections.index'));

        $asset->refresh();

        $this->assertSame(AssetStatus::Available, $asset->status);
        $this->assertSame('good', $asset->condition_status->value);
    }

    public function test_index_renders_summary_and_record(): void
    {
        $asset = $this->borrowedAsset();

        $this->actingAs($this->technician)
            ->post(route('admin.inspections.store'), [
                'asset_id' => $asset->id,
                'result' => InspectionResult::NeedsRepair->value,
                'notes' => 'Sensor berdebu.',
            ]);

        $this->actingAs($this->technician)
            ->get(route('admin.inspections.index'))
            ->assertOk()
            ->assertSee($asset->code)
            ->assertSee('Sensor berdebu.');
    }

    private function borrowedAsset(): Asset
    {
        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Borrowed,
            'condition_status' => 'good',
            'purchase_price' => 1_000_000,
        ]);

        $booking = Booking::create([
            'code' => 'LDR-BK-TEST-'.fake()->unique()->numerify('####'),
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'status' => BookingStatus::Returned,
            'purpose' => 'Uji inspeksi',
            'requested_start_date' => Carbon::parse('2026-12-01'),
            'requested_end_date' => Carbon::parse('2026-12-03'),
        ]);

        $booking->items()->create([
            'asset_id' => $asset->id,
            'status' => 'returned',
        ]);

        return $asset;
    }
}
