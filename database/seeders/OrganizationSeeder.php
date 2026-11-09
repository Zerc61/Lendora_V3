<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::updateOrCreate(
            ['code' => 'LDR'],
            [
                'name' => 'Unit Kampus SMK Negeri 1',
                'status' => 'active',
            ],
        );
    }
}
