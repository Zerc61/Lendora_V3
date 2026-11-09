<?php

namespace App\Services;

use App\Enums\PenaltyStatus;
use App\Models\Penalty;
use App\Models\User;

class PenaltyService
{
    public function summaryFor(User $user): array
    {
        $penalties = Penalty::query()
            ->where('borrower_id', $user->id)
            ->whereIn('status', [
                PenaltyStatus::WaitingPayment->value,
                PenaltyStatus::Partial->value,
                PenaltyStatus::Overdue->value,
                PenaltyStatus::InstallmentRequested->value,
            ])
            ->get();

        $totalAmount = $penalties->sum('total_amount');
        $paidAmount = $penalties->sum(fn (Penalty $penalty): int => $penalty->paidAmount());
        $remainingAmount = $penalties->sum(
            fn (Penalty $penalty): int => $penalty->remainingAmount(),
        );

        return [
            'total_penalties' => $penalties->count(),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
        ];
    }
}