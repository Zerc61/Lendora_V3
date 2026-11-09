<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Penalty;

class PaymentService
{
    public function summaryFor(Penalty $penalty): array
    {
        $payments = $penalty->payments()->get();

        $totalPaid = $payments
            ->where('status', \App\Enums\PaymentStatus::Verified)
            ->sum('amount');

        return [
            'total_paid' => $totalPaid,
            'total_pending' => $payments
                ->whereIn('status', [\App\Enums\PaymentStatus::Pending, \App\Enums\PaymentStatus::WaitingVerification])
                ->sum('amount'),
            'remaining' => max(0, $penalty->total_amount - $totalPaid),
            'is_fully_paid' => $penalty->status === \App\Enums\PenaltyStatus::Paid,
        ];
    }
}