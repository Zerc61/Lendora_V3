<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
            SettingSeeder::class,
            DemoTransactionSeeder::class,
        ]);
    }
}
