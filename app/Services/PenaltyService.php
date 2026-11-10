<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use App\Models\Penalty;
use App\Models\User;

class PenaltyService
{
    public function __construct(private readonly ActivityLogger $logger) {}

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

    public function syncStatus(Penalty $penalty): Penalty
    {
        if (in_array($penalty->status, [PenaltyStatus::Waived], true)) {
            return $penalty;
        }

        $paid = (int) $penalty->payments()
            ->where('status', PaymentStatus::Verified->value)
            ->sum('amount');

        if ($paid >= $penalty->total_amount && $penalty->total_amount > 0) {
            $penalty->update([
                'status' => PenaltyStatus::Paid,
                'paid_at' => $penalty->paid_at ?? now(),
                'recovery_step' => 3,
            ]);
        } elseif ($paid > 0) {
            $penalty->update(['status' => PenaltyStatus::Partial]);
        } elseif ($penalty->due_at !== null && $penalty->due_at->isPast()) {
            $penalty->update(['status' => PenaltyStatus::Overdue]);
        }

        return $penalty->fresh(['payments']);
    }

    public function escalate(Penalty $penalty, User $actor): Penalty
    {
        $penalty->update([
            'escalated_at' => now(),
            'escalated_by' => $actor->id,
        ]);

        $this->logger->log(
            'bap.escalated',
            $penalty,
            "BAP {$penalty->bap_number} dieskalasi ke Organization Admin.",
            $actor,
        );

        return $penalty->fresh(['escalator']);
    }
}
