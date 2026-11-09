<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategoryAndAssetSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
        ]);
    }

    public function test_organization_admin_finds_assets_and_users(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('admin.search', ['q' => 'LDR-CAM']))
            ->assertOk()
            ->assertSee('LDR-CAM-0001');

        $results = $response->viewData('results');

        $this->assertNotEmpty($results['assets']);
        $this->assertArrayHasKey('users', $results->all());

        $this->actingAs($admin)
            ->get(route('admin.search', ['q' => 'operator@lendora.test']))
            ->assertOk()
            ->assertSee('operator@lendora.test');
    }

    public function test_admin_prodi_gets_read_only_asset_links(): void
    {
        $prodi = User::where('email', 'adminprodi@lendora.test')->firstOrFail();

        $this->actingAs($prodi)
            ->get(route('admin.search', ['q' => 'LDR-CAM-0001']))
            ->assertOk()
            ->assertSee('search=LDR-CAM-0001')
            ->assertDontSee('/assets/1/edit');
    }

    public function test_operator_does_not_get_user_results(): void
    {        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $response = $this->actingAs($operator)
            ->get(route('admin.search', ['q' => 'operator']))
            ->assertOk();

        $results = $response->viewData('results');

        $this->assertArrayNotHasKey('users', $results->all());
        $this->assertArrayNotHasKey('categories', $results->all());
    }

    public function test_search_is_scoped_to_own_organization(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $other = Organization::create([
            'name' => 'Organisasi Lain',
            'code' => 'OTH',
        ]);

        $category = \App\Models\Category::create([
            'organization_id' => $other->id,
            'name' => 'Kamera Lain',
            'slug' => 'kamera-lain',
        ]);

        Asset::create([
            'organization_id' => $other->id,
            'category_id' => $category->id,
            'code' => 'OTH-CAM-9999',
            'name' => 'Kamera Organisasi Lain',
            'status' => 'available',
            'condition_status' => 'good',
            'purchase_price' => 1000000,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.search', ['q' => 'OTH-CAM']))
            ->assertOk();

        $this->assertTrue($response->viewData('results')['assets']->isEmpty());
    }

    public function test_short_term_does_not_search(): void
    {
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('admin.search', ['q' => 'L']))
            ->assertOk();

        $this->assertFalse($response->viewData('searched'));
    }
}
