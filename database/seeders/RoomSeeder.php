<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $rooms = [
            ['code' => 'R-01', 'name' => 'Studio AV Terpadu 1', 'capacity' => 40, 'notes' => 'Akustik Class A'],
            ['code' => 'R-02', 'name' => 'Studio Green Screen 2', 'capacity' => 25, 'notes' => 'Cyc-Wall 8x6m'],
            ['code' => 'R-03', 'name' => 'Lab Sound Mixing & Foley', 'capacity' => 20, 'notes' => 'Dolby Atmos Suite'],
            ['code' => 'R-04', 'name' => 'Dry Box Rental Pick', 'capacity' => 5, 'notes' => 'Penyimpanan unit'],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['organization_id' => $organization->id, 'code' => $room['code']],
                [...$room, 'is_active' => true],
            );
        }
    }
}
