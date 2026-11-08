<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetPhoto;
use App\Models\Category;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryAndAssetSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $roots = [
            'kamera-mirrorless' => ['name' => 'Kamera Mirrorless', 'children' => [
                'full-frame' => 'Full-frame',
                'aps-c' => 'APS-C',
            ]],
            'lensa' => ['name' => 'Lensa', 'children' => [
                'zoom' => 'Zoom',
                'prime' => 'Prime',
            ]],
            'tripod' => ['name' => 'Tripod', 'children' => []],
            'lampu' => ['name' => 'Lampu', 'children' => []],
        ];

        $categories = [];

        foreach ($roots as $slug => $root) {
            $category = Category::updateOrCreate(
                ['organization_id' => $organization->id, 'slug' => $slug],
                ['name' => $root['name'], 'parent_id' => null],
            );

            $categories[$slug] = $category;

            foreach ($root['children'] as $childSlug => $childName) {
                $categories[$childSlug] = Category::updateOrCreate(
                    ['organization_id' => $organization->id, 'slug' => $childSlug],
                    ['name' => $childName, 'parent_id' => $category->id],
                );
            }
        }

        $assets = [
            ['code' => 'LDR-CAM-0001', 'category' => 'full-frame', 'name' => 'Sony A7 III', 'brand' => 'Sony', 'model' => 'ILCE-7M3', 'serial_number' => 'SN-A7III-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-03-15', 'purchase_price' => 22000000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-CAM-0002', 'category' => 'full-frame', 'name' => 'Sony A7 III', 'brand' => 'Sony', 'model' => 'ILCE-7M3', 'serial_number' => 'SN-A7III-002', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-03-15', 'purchase_price' => 22000000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-CAM-0003', 'category' => 'full-frame', 'name' => 'Canon EOS R6', 'brand' => 'Canon', 'model' => 'EOS R6', 'serial_number' => 'SN-R6-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-01-10', 'purchase_price' => 24500000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-CAM-0004', 'category' => 'full-frame', 'name' => 'Canon EOS R6', 'brand' => 'Canon', 'model' => 'EOS R6', 'serial_number' => 'SN-R6-002', 'status' => 'reserved', 'condition_status' => 'minor_damage', 'purchase_date' => '2023-01-10', 'purchase_price' => 24500000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-CAM-0005', 'category' => 'aps-c', 'name' => 'Fujifilm X-T5', 'brand' => 'Fujifilm', 'model' => 'X-T5', 'serial_number' => 'SN-XT5-001', 'status' => 'maintenance', 'condition_status' => 'good', 'purchase_date' => '2023-06-20', 'purchase_price' => 19500000, 'location' => 'Ruang Perawatan'],
            ['code' => 'LDR-CAM-0006', 'category' => 'full-frame', 'name' => 'Nikon Z6 II', 'brand' => 'Nikon', 'model' => 'Z6 II', 'serial_number' => 'SN-Z6II-001', 'status' => 'retired', 'condition_status' => 'damaged', 'purchase_date' => '2021-11-05', 'purchase_price' => 21000000, 'location' => 'Gudang'],
            ['code' => 'LDR-LEN-0001', 'category' => 'zoom', 'name' => 'Sony FE 24-70mm F2.8 GM', 'brand' => 'Sony', 'model' => 'SEL2470GM', 'serial_number' => 'SN-2470-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-03-15', 'purchase_price' => 28000000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-LEN-0002', 'category' => 'prime', 'name' => 'Canon RF 50mm F1.8 STM', 'brand' => 'Canon', 'model' => 'RF50', 'serial_number' => 'SN-RF50-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-01-10', 'purchase_price' => 3200000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-LEN-0003', 'category' => 'zoom', 'name' => 'Sigma 18-35mm F1.8 Art', 'brand' => 'Sigma', 'model' => '18-35 Art', 'serial_number' => 'SN-SIG1835-001', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-08-01', 'purchase_price' => 9800000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-LEN-0004', 'category' => 'zoom', 'name' => 'Tamron 70-200mm F2.8 G2', 'brand' => 'Tamron', 'model' => 'A025', 'serial_number' => 'SN-TAM70200-001', 'status' => 'maintenance', 'condition_status' => 'minor_damage', 'purchase_date' => '2021-09-12', 'purchase_price' => 16500000, 'location' => 'Ruang Perawatan'],
            ['code' => 'LDR-TRI-0001', 'category' => 'tripod', 'name' => 'Manfrotto MT055XPRO3', 'brand' => 'Manfrotto', 'model' => 'MT055XPRO3', 'serial_number' => 'SN-MTF055-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-02-02', 'purchase_price' => 4100000, 'location' => 'Gudang'],
            ['code' => 'LDR-TRI-0002', 'category' => 'tripod', 'name' => 'Gitzo GT1545T', 'brand' => 'Gitzo', 'model' => 'GT1545T', 'serial_number' => 'SN-GTZ1545-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-04-18', 'purchase_price' => 7800000, 'location' => 'Gudang'],
            ['code' => 'LDR-TRI-0003', 'category' => 'tripod', 'name' => 'Benro A2573FS4', 'brand' => 'Benro', 'model' => 'A2573FS4', 'serial_number' => 'SN-BEN2573-001', 'status' => 'retired', 'condition_status' => 'good', 'purchase_date' => '2019-07-21', 'purchase_price' => 2300000, 'location' => 'Gudang'],
            ['code' => 'LDR-TRI-0004', 'category' => 'tripod', 'name' => 'DJI RS 3', 'brand' => 'DJI', 'model' => 'RS 3', 'serial_number' => 'SN-DJIRS3-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2023-08-09', 'purchase_price' => 8500000, 'location' => 'Gudang'],
            ['code' => 'LDR-LMP-0001', 'category' => 'lampu', 'name' => 'Godox SL60W', 'brand' => 'Godox', 'model' => 'SL60W', 'serial_number' => 'SN-SL60W-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-05-11', 'purchase_price' => 1900000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-LMP-0002', 'category' => 'lampu', 'name' => 'Godox AD200 Pro', 'brand' => 'Godox', 'model' => 'AD200 Pro', 'serial_number' => 'SN-AD200-001', 'status' => 'borrowed', 'condition_status' => 'good', 'purchase_date' => '2022-05-11', 'purchase_price' => 5200000, 'location' => 'Studio Foto'],
            ['code' => 'LDR-LMP-0003', 'category' => 'lampu', 'name' => 'Aputure 120D II', 'brand' => 'Aputure', 'model' => '120D II', 'serial_number' => 'SN-AP120D-001', 'status' => 'maintenance', 'condition_status' => 'damaged', 'purchase_date' => '2021-12-01', 'purchase_price' => 6900000, 'location' => 'Ruang Perawatan'],
            ['code' => 'LDR-LMP-0004', 'category' => 'lampu', 'name' => 'Godox Softbox 90x90', 'brand' => 'Godox', 'model' => 'SB-90', 'serial_number' => 'SN-SB90-001', 'status' => 'available', 'condition_status' => 'good', 'purchase_date' => '2022-05-11', 'purchase_price' => 750000, 'location' => 'Studio Foto'],
        ];

        foreach ($assets as $data) {
            $categorySlug = $data['category'];
            unset($data['category']);

            $asset = Asset::updateOrCreate(
                ['code' => $data['code']],
                [
                    ...$data,
                    'organization_id' => $organization->id,
                    'category_id' => $categories[$categorySlug]->id,
                    'description' => "Unit demo {$data['name']} untuk keperluan pengujian Lendora.",
                ],
            );

            $path = "assets/{$asset->code}/placeholder.svg";

            Storage::disk('public')->put($path, $this->placeholderSvg($asset->code, $asset->name));

            AssetPhoto::updateOrCreate(
                ['asset_id' => $asset->id, 'path' => $path],
                ['is_primary' => true, 'sort_order' => 0],
            );
        }
    }

    private function placeholderSvg(string $code, string $name): string
    {
        $code = Str::of($code)->replace(['&', '<', '>'], '')->value();
        $name = Str::of($name)->replace(['&', '<', '>'], '')->value();

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
    <rect width="800" height="600" fill="#F5F5F5"/>
    <rect x="250" y="190" width="300" height="190" rx="24" fill="#FFF7ED" stroke="#F97316" stroke-width="6"/>
    <circle cx="400" cy="285" r="62" fill="none" stroke="#F97316" stroke-width="10"/>
    <circle cx="400" cy="285" r="30" fill="#F97316"/>
    <text x="400" y="455" font-family="sans-serif" font-size="30" font-weight="700" fill="#262626" text-anchor="middle">{$code}</text>
    <text x="400" y="495" font-family="sans-serif" font-size="22" fill="#4B5563" text-anchor="middle">{$name}</text>
</svg>
SVG;
    }
}
