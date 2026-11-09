<?php

namespace Database\Seeders;

use App\Enums\PenaltyStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenaltySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('code', 'LDR')->firstOrFail();
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        // Denda terbuka untuk borrower1 agar akun demo utama (Ani) tetap aktif.
        $openBooking = Booking::where('code', 'LDR-BK-2026-0001')->first();
        $openBorrower = User::where('email', 'borrower1@lendora.test')->first();

        if ($openBooking !== null && $openBorrower !== null) {
            Penalty::updateOrCreate(
                ['bap_number' => 'LDR-BAP-2026-0004'],
                [
                    'organization_id' => $organization->id,
                    'booking_id' => $openBooking->id,
                    'asset_id' => Asset::where('code', 'LDR-CAM-0001')->value('id'),
                    'borrower_id' => $openBorrower->id,
                    'incident_at' => now()->subDays(2)->setTime(16, 45),
                    'inspector_name' => 'Budi Santoso (Loket A)',
                    'damage_description' => 'Cacat sensor dan mount bayonet bengkok.',
                    'repair_subtotal' => 3_450_000,
                    'subsidy_percent' => 10,
                    'subsidy_amount' => 345_000,
                    'admin_fee' => 0,
                    'total_amount' => 3_105_000,
                    'status' => PenaltyStatus::WaitingPayment,
                    'due_at' => now()->addDays(4),
                    'recovery_step' => 1,
                    'created_by' => $operator->id,
                ],
            );

            $openBorrower->update(['status' => 'suspended']);
        }

        // Riwayat denda lunas untuk Ani (tidak menangguhkan akun).
        $paidBooking = Booking::where('code', 'LDR-BK-2026-0103')->first();
        $ani = User::where('email', 'ani@lendora.test')->first();

        if ($paidBooking !== null && $ani !== null) {
            Penalty::updateOrCreate(
                ['bap_number' => 'LDR-BAP-2026-0003'],
                [
                    'organization_id' => $organization->id,
                    'booking_id' => $paidBooking->id,
                    'asset_id' => Asset::where('code', 'LDR-CAM-0009')->value('id'),
                    'borrower_id' => $ani->id,
                    'incident_at' => now()->subDays(15)->setTime(14, 45),
                    'inspector_name' => 'Budi Santoso (Loket A)',
                    'damage_description' => 'Lecet ringan pada body unit.',
                    'repair_subtotal' => 250_000,
                    'subsidy_percent' => 10,
                    'subsidy_amount' => 25_000,
                    'admin_fee' => 0,
                    'total_amount' => 225_000,
                    'status' => PenaltyStatus::Paid,
                    'due_at' => now()->subDays(11),
                    'paid_at' => now()->subDays(12),
                    'recovery_step' => 3,
                    'created_by' => $operator->id,
                ],
            );
        }
    }
}
