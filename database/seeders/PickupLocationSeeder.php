<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PickupLocation;
use Illuminate\Database\Seeder;

class PickupLocationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $locations = [
            ['name' => 'Loket A — Studio AV', 'address' => 'Gedung C, Lantai 1', 'operational_hours' => 'Senin–Jumat 08:30–16:00 WIB', 'sort_order' => 1],
            ['name' => 'Loket B — Bengkel Teknisi', 'address' => 'Gedung C, Lantai 2', 'operational_hours' => 'Senin–Jumat 09:00–15:00 WIB', 'sort_order' => 2],
            ['name' => 'Loket Keuangan Kampus', 'address' => 'Gedung A, Lantai 1', 'operational_hours' => 'Senin–Jumat 08:00–15:00 WIB', 'sort_order' => 3],
        ];

        foreach ($locations as $location) {
            PickupLocation::updateOrCreate(
                ['organization_id' => $organization->id, 'name' => $location['name']],
                [...$location, 'is_active' => true],
            );
        }
    }
}
