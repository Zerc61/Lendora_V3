<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetComponent;
use Illuminate\Database\Seeder;

class AssetComponentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Asset::query()->cursor() as $asset) {
            $components = [
                ['name' => 'Body Unit', 'serial_number' => $asset->serial_number, 'notes' => 'Unit utama'],
                ['name' => 'Baterai', 'serial_number' => 'BAT-'.$asset->code, 'notes' => 'Baterai bawaan'],
                ['name' => 'Tas Penyimpanan', 'serial_number' => null, 'notes' => 'Tas unit'],
            ];

            foreach ($components as $index => $component) {
                AssetComponent::updateOrCreate(
                    ['asset_id' => $asset->id, 'name' => $component['name']],
                    [
                        ...$component,
                        'quantity' => 1,
                        'condition_status' => 'good',
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}
