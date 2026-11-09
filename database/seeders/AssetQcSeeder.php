<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetQcCheck;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetQcSeeder extends Seeder
{
    public function run(): void
    {
        $technician = User::where('email', 'technician@lendora.test')->firstOrFail();

        foreach (Asset::query()->cursor() as $asset) {
            $checkedAt = now()->subDays(7);

            AssetQcCheck::updateOrCreate(
                ['asset_id' => $asset->id, 'checked_at' => $checkedAt],
                [
                    'technician_id' => $technician->id,
                    'result' => 'ok',
                    'sensor_cleanliness' => 'Bersih, tanpa debu',
                    'shutter_accuracy' => 'Akurasi dalam toleransi',
                    'firmware_version' => 'v2.10',
                    'notes' => 'Pemeriksaan rutin bulanan.',
                    'next_due_at' => now()->addMonth(),
                ],
            );
        }
    }
}
