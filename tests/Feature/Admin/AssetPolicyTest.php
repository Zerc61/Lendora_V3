<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use App\Services\AssetService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetPolicyTest extends TestCase
{
    use RefreshDatabase;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $admin = $this->user('admin@lendora.test');
        $category = Category::create([
            'organization_id' => $admin->organization_id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);

        $this->asset = app(AssetService::class)->create(
            $admin->organization,
            $category,
            ['name' => 'Sony A7 III', 'serial_number' => 'SN-A7III-001'],
        );
    }

    public function test_operator_can_view_assets_but_cannot_modify_them(): void
    {
        $operator = $this->user('operator@lendora.test');

        $this->actingAs($operator)
            ->get(route('admin.assets.index'))
            ->assertOk()
            ->assertSee('Sony A7 III');

        $this->actingAs($operator)
            ->post(route('admin.assets.store'), $this->payload())
            ->assertForbidden();

        $this->actingAs($operator)
            ->put(route('admin.assets.update', $this->asset), $this->payload())
            ->assertForbidden();
    }

    public function test_technician_can_view_assets_but_cannot_manage_them(): void
    {
        $this->actingAs($this->user('technician@lendora.test'))
            ->get(route('admin.assets.index'))
            ->assertOk();

        $this->actingAs($this->user('technician@lendora.test'))
            ->post(route('admin.assets.store'), $this->payload())
            ->assertForbidden();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function payload(): array
    {
        return [
            'category_id' => $this->asset->category_id,
            'name' => 'Kamera Baru',
            'serial_number' => 'SN-BARU-001',
        ];
    }
}
