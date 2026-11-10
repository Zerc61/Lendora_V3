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
            BorrowerShowcaseSeeder::class,
            SettingSeeder::class,
            CourseSeeder::class,
            PickupLocationSeeder::class,
            DemoTransactionSeeder::class,
            AnnouncementSeeder::class,
            FaqSeeder::class,
            DocumentSeeder::class,
            AssetComponentSeeder::class,
            AssetQcSeeder::class,
            AssetBlockSeeder::class,
            BorrowerFavoriteSeeder::class,
            BorrowerCertificationSeeder::class,
            BookingExtensionSeeder::class,
            PenaltySeeder::class,
            PaymentSeeder::class,
            OperatorDemoSeeder::class,
        ]);
    }
}
