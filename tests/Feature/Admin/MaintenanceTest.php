<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $admin;

    private User $technician;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->technician = User::where('email', 'technician@lendora.test')->firstOrFail();
        $this->organization = $this->admin->organization;

        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_assigned_technician_starts_task(): void
    {
        $maintenance = $this->maintenance();

        $this->actingAs($this->technician)
            ->post(route('admin.maintenances.start', $maintenance))
            ->assertRedirect(route('admin.maintenances.index'));

        $maintenance->refresh();

        $this->assertSame(MaintenanceStatus::InProgress, $maintenance->status);
        $this->assertNotNull($maintenance->started_at);
        $this->assertSame(AssetStatus::Maintenance, $maintenance->asset->status);
    }

    public function test_finish_with_good_condition_returns_asset_to_available(): void
    {
        $maintenance = $this->maintenance(MaintenanceStatus::InProgress);

        $this->actingAs($this->technician)
            ->post(route('admin.maintenances.finish.store', $maintenance), [
                'result_notes' => 'Sensor dibersihkan.',
                'cost' => 150000,
                'condition_good' => '1',
            ])
            ->assertRedirect(route('admin.maintenances.index'));

        $maintenance->refresh();

        $this->assertSame(MaintenanceStatus::Done, $maintenance->status);
        $this->assertSame(150000, $maintenance->cost);
        $this->assertSame(AssetStatus::Available, $maintenance->asset->status);
        $this->assertSame('good', $maintenance->asset->condition_status->value);
    }

    public function test_organization_admin_assigns_and_cancels_task(): void
    {
        $maintenance = $this->maintenance(assigned: false);

        $this->actingAs($this->admin)
            ->put(route('admin.maintenances.update', $maintenance), [
                'assigned_to' => $this->technician->id,
                'description' => $maintenance->description,
            ])
            ->assertRedirect(route('admin.maintenances.index'));

        $this->assertSame($this->technician->id, $maintenance->fresh()->assigned_to);

        $this->actingAs($this->admin)
            ->delete(route('admin.maintenances.destroy', $maintenance))
            ->assertRedirect(route('admin.maintenances.index'));

        $this->assertSame(MaintenanceStatus::Cancelled, $maintenance->fresh()->status);
    }

    public function test_index_renders_summary_and_task(): void
    {
        $maintenance = $this->maintenance(MaintenanceStatus::InProgress);

        $this->actingAs($this->admin)
            ->get(route('admin.maintenances.index'))
            ->assertOk()
            ->assertSee($maintenance->asset->code)
            ->assertSee('Kamera Uji');
    }

    public function test_technician_dashboard_and_finish_form_render_work_order(): void
    {
        $maintenance = $this->maintenance(MaintenanceStatus::InProgress);

        $this->actingAs($this->technician)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tugas saya')
            ->assertSee('Perbaikan lensa.');

        $this->actingAs($this->technician)
            ->get(route('admin.maintenances.finish', $maintenance))
            ->assertOk()
            ->assertSee('Lembar kerja')
            ->assertSee('Perbaikan lensa.');
    }

    private function maintenance(MaintenanceStatus $status = MaintenanceStatus::Scheduled, bool $assigned = true): Maintenance
    {
        $asset = Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $this->category->id,
            'code' => 'LDR-CAM-'.fake()->unique()->numerify('####'),
            'name' => 'Kamera Uji',
            'status' => AssetStatus::Maintenance,
            'condition_status' => 'minor_damage',
            'purchase_price' => 1_000_000,
        ]);

        return Maintenance::create([
            'asset_id' => $asset->id,
            'reported_by' => $this->admin->id,
            'assigned_to' => $assigned ? $this->technician->id : null,
            'status' => $status,
            'description' => 'Perbaikan lensa.',
            'started_at' => $status === MaintenanceStatus::InProgress ? now() : null,
        ]);
    }
}
