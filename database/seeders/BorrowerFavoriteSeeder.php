<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetFavorite;
use App\Models\User;
use Illuminate\Database\Seeder;

class BorrowerFavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $borrower = User::where('email', 'ani@lendora.test')->firstOrFail();

        foreach (['LDR-CAM-0007', 'LDR-LEN-0005', 'LDR-CAM-0009'] as $code) {
            $asset = Asset::where('code', $code)->first();

            if ($asset === null) {
                continue;
            }

            AssetFavorite::updateOrCreate([
                'user_id' => $borrower->id,
                'asset_id' => $asset->id,
            ]);
        }
    }
}
