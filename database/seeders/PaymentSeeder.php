<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $operator = User::where('email', 'operator@lendora.test')->firstOrFail();

        $openPenalty = Penalty::where('bap_number', 'LDR-BAP-2026-0004')->first();

        if ($openPenalty !== null) {
            Payment::updateOrCreate(
                ['penalty_id' => $openPenalty->id, 'method' => PaymentMethod::VaBni->value],
                [
                    'borrower_id' => $openPenalty->borrower_id,
                    'amount' => 1_000_000,
                    'status' => PaymentStatus::WaitingVerification,
                    'reference_number' => '988-0012-2207412089',
                    'paid_at' => now()->subHours(6),
                    'notes' => 'Bukti transfer diunggah borrower.',
                ],
            );
        }

        $paidPenalty = Penalty::where('bap_number', 'LDR-BAP-2026-0003')->first();

        if ($paidPenalty !== null) {
            Payment::updateOrCreate(
                ['penalty_id' => $paidPenalty->id, 'method' => PaymentMethod::Qris->value],
                [
                    'borrower_id' => $paidPenalty->borrower_id,
                    'amount' => 225_000,
                    'status' => PaymentStatus::Verified,
                    'reference_number' => 'QRIS-2026-0003',
                    'paid_at' => now()->subDays(12),
                    'verified_at' => now()->subDays(12),
                    'verified_by' => $operator->id,
                ],
            );
        }
    }
}
