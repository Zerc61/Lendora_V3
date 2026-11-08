<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();

        $settings = [
            'max_loan_days' => '7',
            'operator_approval_limit' => '5000000',
            'return_reminder_enabled' => '1',
            'borrower_announcement' => 'Selamat datang di Lendora. Ajukan peminjaman peralatan melalui katalog.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['organization_id' => $organization->id, 'key' => $key],
                ['value' => $value],
            );
        }
    }
}
