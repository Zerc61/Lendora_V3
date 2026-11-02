<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_assets(): void
    {
        $category = AssetCategory::factory()->create();
        Asset::factory()->count(2)->for($category, 'category')->create();

        $this->assertCount(2, $category->assets);
        $this->assertTrue($category->assets->first()->category->is($category));
    }

    public function test_asset_defaults_to_available_and_good(): void
    {
        $asset = Asset::create([
            'asset_category_id' => AssetCategory::factory()->create()->id,
            'code' => 'AST-0001',
            'name' => 'Kamera',
        ]);

        $asset->refresh();

        $this->assertSame('available', $asset->status);
        $this->assertSame('good', $asset->condition);
    }

    public function test_soft_deleted_asset_disappears_from_queries(): void
    {
        $asset = Asset::factory()->create();
        $asset->delete();

        $this->assertNull(Asset::find($asset->id));
        $this->assertNotNull(Asset::withTrashed()->find($asset->id));
    }
}
