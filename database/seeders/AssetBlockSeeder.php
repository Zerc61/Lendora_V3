<?php

namespace Database\Seeders;

use App\Enums\AssetBlockType;
use App\Models\Asset;
use App\Models\AssetBlock;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetBlockSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $admin = User::where('email', 'admin@lendora.test')->firstOrFail();

        $blocks = [
            [
                'asset_id' => null,
                'type' => AssetBlockType::Closure,
                'starts_at' => now()->addDays(10)->setTime(8, 0),
                'ends_at' => now()->addDays(11)->setTime(17, 0),
                'reason' => 'Libur unit dan pemeliharaan ruang penyimpanan.',
            ],
            [
                'asset_id' => Asset::where('code', 'LDR-CAM-0001')->value('id'),
                'type' => AssetBlockType::Practical,
                'starts_at' => now()->addDays(3)->setTime(8, 0),
                'ends_at' => now()->addDays(3)->setTime(12, 0),
                'reason' => 'Praktikum Fotografi Dasar DKV-201.',
            ],
            [
                'asset_id' => Asset::where('code', 'LDR-CAM-0005')->value('id'),
                'type' => AssetBlockType::Calibration,
                'starts_at' => now()->subDay()->setTime(8, 0),
                'ends_at' => now()->addDays(2)->setTime(17, 0),
                'reason' => 'Kalibrasi fokus dan pembersihan sensor.',
            ],
        ];

        foreach ($blocks as $block) {
            AssetBlock::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'asset_id' => $block['asset_id'],
                    'type' => $block['type'],
                    'starts_at' => $block['starts_at'],
                ],
                [
                    'ends_at' => $block['ends_at'],
                    'reason' => $block['reason'],
                    'created_by' => $admin->id,
                ],
            );
        }
    }
}
