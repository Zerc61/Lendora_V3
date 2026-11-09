<?php

namespace Tests\Feature\Admin;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Organization;
use App\Models\User;
use App\Services\AssetService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Organization $organization;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        Storage::fake('public');

        $this->admin = User::where('email', 'admin@lendora.test')->firstOrFail();
        $this->organization = $this->admin->organization;
        $this->category = Category::create([
            'organization_id' => $this->organization->id,
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_org_admin_creates_asset_with_generated_code_and_photo(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assets.store'), $this->assetPayload([
            'photos' => [UploadedFile::fake()->image('kamera.jpg')],
        ]));

        $response->assertRedirect(route('admin.assets.index'));

        $asset = Asset::firstOrFail();

        $this->assertSame('LDR-CAM-0001', $asset->code);
        $this->assertSame(AssetStatus::Available, $asset->status);
        $this->assertDatabaseHas('asset_photos', [
            'asset_id' => $asset->id,
            'is_primary' => true,
            'sort_order' => 1,
        ]);
        Storage::disk('public')->assertExists($asset->photos()->firstOrFail()->path);
    }

    public function test_org_admin_updates_and_soft_deletes_asset(): void
    {
        $asset = $this->createAsset();

        $this->actingAs($this->admin)->get(route('admin.assets.create'))->assertOk();
        $this->actingAs($this->admin)
            ->get(route('admin.assets.edit', $asset))
            ->assertOk()
            ->assertSee($asset->code);

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->assetPayload([
                'name' => 'Sony A7 IV',
                'serial_number' => $asset->serial_number,
            ]))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertSame('Sony A7 IV', $asset->fresh()->name);

        $this->actingAs($this->admin)
            ->delete(route('admin.assets.destroy', $asset))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertSoftDeleted($asset);

        Asset::withTrashed()->findOrFail($asset->id)->restore();
        $this->assertNotSoftDeleted(Asset::withTrashed()->findOrFail($asset->id));
    }

    public function test_duplicate_serial_number_and_code_are_rejected_with_indonesian_messages(): void
    {
        $asset = $this->createAsset(['serial_number' => 'SN-DUPLICATE-001']);

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->assetPayload([
                'name' => 'Kamera Kedua',
                'serial_number' => 'SN-DUPLICATE-001',
            ]))
            ->assertSessionHasErrors('serial_number');

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->assetPayload([
                'name' => 'Kamera Ketiga',
                'code' => $asset->code,
            ]))
            ->assertSessionHasErrors('code');

        $this->assertSame(1, Asset::count());
    }

    public function test_asset_index_filters_and_paginates_server_side(): void
    {
        for ($index = 1; $index <= 16; $index++) {
            $this->createAsset([
                'name' => "Aset Uji {$index}",
                'serial_number' => "SN-UJI-{$index}",
            ]);
        }

        $this->createAsset(['name' => 'Kamera Khusus', 'serial_number' => 'SN-KHUSUS-001']);

        $this->actingAs($this->admin)
            ->get(route('admin.assets.index'))
            ->assertOk()
            ->assertViewHas('assets', fn ($assets) => $assets->count() === 15 && $assets->total() === 17);

        $this->actingAs($this->admin)
            ->get(route('admin.assets.index', ['search' => 'Kamera Khusus']))
            ->assertOk()
            ->assertSee('Kamera Khusus')
            ->assertDontSee('Aset Uji 1');
    }

    public function test_org_admin_manages_categories(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Lensa',
                'description' => 'Lensa kamera',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Lensa')->firstOrFail();
        $this->assertSame('lensa', $category->slug);

        $this->actingAs($this->admin)->get(route('admin.categories.index'))->assertOk()->assertSee('Lensa');
        $this->actingAs($this->admin)->get(route('admin.categories.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.categories.edit', $category))->assertOk();

        $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Lensa Kamera',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Lensa Kamera', $category->fresh()->name);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted($category);
    }

    public function test_category_with_children_or_assets_cannot_be_deleted(): void
    {
        $child = Category::create([
            'organization_id' => $this->organization->id,
            'parent_id' => $this->category->id,
            'name' => 'Full-frame',
            'slug' => 'full-frame',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error', __('messages.category.delete_blocked'));

        $this->assertNotSoftDeleted($this->category);

        app(AssetService::class)->create(
            $this->organization,
            $child,
            ['name' => 'Sony A7 III', 'serial_number' => 'SN-A7III-001'],
        );

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $child))
            ->assertSessionHas('error', __('messages.category.delete_blocked'));

        $this->assertNotSoftDeleted($child);
    }

    private function createAsset(array $overrides = []): Asset
    {
        return app(AssetService::class)->create(
            $this->organization,
            $this->category,
            [
                'name' => 'Sony A7 III',
                'brand' => 'Sony',
                'model' => 'ILCE-7M3',
                'serial_number' => 'SN-A7III-001',
                ...$overrides,
            ],
        );
    }

    private function assetPayload(array $overrides = []): array
    {
        return [
            'category_id' => $this->category->id,
            'name' => 'Sony A7 III',
            'brand' => 'Sony',
            'model' => 'ILCE-7M3',
            'serial_number' => 'SN-A7III-001',
            'description' => 'Kamera mirrorless demo.',
            'condition_status' => 'good',
            'purchase_date' => '2023-01-10',
            'purchase_price' => 22000000,
            'location' => 'Studio Foto',
            ...$overrides,
        ];
    }
}
