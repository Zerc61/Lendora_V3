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
            'semester_name' => 'Ganjil 2026/2027',
            'semester_start' => '2026-08-01',
            'semester_end' => '2026-12-31',
            'semester_quota_hours' => '25',
            'max_parallel_units' => '3',
            'max_addon_units' => '3',
            'late_fee_per_hour' => '10000',
            'late_grace_minutes' => '15',
            'compliance_penalty_per_hour' => '5',
            'damage_subsidy_percent' => '10',
            'pickup_open_time' => '08:30',
            'return_close_time' => '16:00',
            'operator_contact_phone' => 'Ext. 402',
            'operator_contact_whatsapp' => '+62 812-3456-7890',
            'operational_hours' => 'Senin–Jumat 08:00–17:00 WIB',
            'lab_status' => 'open',
            'queue_average_minutes' => '2',
            'sop_version' => 'V2.4',
            'bap_prefix' => 'LDR-BAP',
            'va_bank_name' => 'BNI Kampus',
            'va_prefix' => '988-0012',
            'finance_counter_name' => 'Loket Keuangan Kampus',
            'penalty_due_days' => '4',
            'installment_max' => '3',
            'suspension_on_penalty' => '1',
            'recovery_step_2_title' => 'Workshop SOP Lab Outdoor (1 Hari)',
            'recovery_step_3_title' => 'Otorisasi Pemulihan oleh Kaprodi',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['organization_id' => $organization->id, 'key' => $key],
                ['value' => $value],
            );
        }
    }
}
