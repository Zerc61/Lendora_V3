<?php

namespace Tests\Feature\Borrower;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $borrower;

    private Organization $organization;

    private Category $camera;

    private Category $lens;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->organization = $this->borrower->organization;

        $this->camera = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
        $this->lens = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Lensa',
            'slug' => 'lensa',
        ]);
    }

    public function test_catalog_hides_maintenance_and_retired_units(): void
    {
        $this->asset('Sony A7 III', 'CAM-1', $this->camera, AssetStatus::Available);
        $this->asset('Canon EOS R6', 'CAM-2', $this->camera, AssetStatus::Reserved);
        $this->asset('Fujifilm X-T5', 'CAM-3', $this->camera, AssetStatus::Maintenance);
        $this->asset('Nikon Z6 II', 'CAM-4', $this->camera, AssetStatus::Retired);

        $response = $this->actingAs($this->borrower)->get(route('borrower.catalog.index'));

        $response->assertOk();
        $response->assertSee('Sony A7 III');
        $response->assertSee('Canon EOS R6');
        $response->assertDontSee('Fujifilm X-T5');
        $response->assertDontSee('Nikon Z6 II');
    }

    public function test_hidden_unit_detail_returns_not_found(): void
    {
        $maintenance = $this->asset('Fujifilm X-T5', 'CAM-3', $this->camera, AssetStatus::Maintenance);

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.show', $maintenance))
            ->assertNotFound();
    }

    public function test_catalog_filters_by_search_and_category(): void
    {
        $this->asset('Sony A7 III', 'CAM-1', $this->camera, AssetStatus::Available);
        $this->asset('Sony FE 24-70mm', 'LEN-1', $this->lens, AssetStatus::Available);
        $this->asset('Canon EOS R6', 'CAM-2', $this->camera, AssetStatus::Available);

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.index', ['search' => 'Sony']))
            ->assertOk()
            ->assertSee('Sony A7 III')
            ->assertSee('Sony FE 24-70mm')
            ->assertDontSee('Canon EOS R6');

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.index', ['category_id' => $this->lens->id]))
            ->assertOk()
            ->assertSee('Sony FE 24-70mm')
            ->assertDontSee('Sony A7 III');
    }

    public function test_available_detail_exposes_availability_flag(): void
    {
        $asset = $this->asset('Sony A7 III', 'CAM-1', $this->camera, AssetStatus::Available);

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.show', $asset))
            ->assertOk()
            ->assertSee('Tersedia');
    }

    private function asset(string $name, string $suffix, Category $category, AssetStatus $status): Asset
    {
        return Asset::create([
            'organization_id' => $this->organization->id,
            'category_id' => $category->id,
            'code' => "LDR-{$suffix}",
            'name' => $name,
            'status' => $status,
            'condition_status' => 'good',
        ]);
    }
}
