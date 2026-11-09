<?php

namespace Tests\Unit;

use App\Enums\AssetStatus;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\AssetService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetServiceTest extends TestCase
{
    use RefreshDatabase;

    private AssetService $service;

    private Organization $organization;

    private Category $category;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->service = app(AssetService::class);
        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->organization = $this->admin->organization;
        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_code_format_increments_per_organization_and_category(): void
    {
        $this->assertSame('LDR-CAM-0001', $this->service->generateCode($this->organization, $this->category));

        $this->service->create($this->organization, $this->category, ['name' => 'Sony A7 III']);

        $this->assertSame('LDR-CAM-0002', $this->service->generateCode($this->organization, $this->category));
    }

    public function test_retire_sets_status_and_logs_activity(): void
    {
        $asset = $this->service->create($this->organization, $this->category, ['name' => 'Sony A7 III']);

        $retired = $this->service->retire($asset, $this->admin);

        $this->assertSame(AssetStatus::Retired, $retired->status);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'asset.status_changed',
            'user_id' => $this->admin->id,
            'subject_type' => $asset::class,
            'subject_id' => $asset->id,
        ]);
    }

    public function test_retire_rejects_non_organization_admin(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $asset = $this->service->create($this->organization, $this->category, ['name' => 'Sony A7 III']);

        $this->expectException(AuthorizationException::class);

        $this->service->retire($asset, $operator);
    }
}
