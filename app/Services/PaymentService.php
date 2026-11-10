<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly ActivityLogger $logger,
        private readonly PenaltyService $penalties,
    ) {}

    public function summaryFor(Penalty $penalty): array
    {
        $payments = $penalty->payments()->get();

        $totalPaid = $payments
            ->where('status', PaymentStatus::Verified)
            ->sum('amount');

        return [
            'total_paid' => $totalPaid,
            'total_pending' => $payments
                ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::WaitingVerification])
                ->sum('amount'),
            'remaining' => max(0, $penalty->total_amount - $totalPaid),
            'is_fully_paid' => $penalty->status === \App\Enums\PenaltyStatus::Paid,
        ];
    }

    public function verify(Payment $payment, User $actor): Payment
    {
        $this->assertStaff($payment, $actor);

        if (! in_array($payment->status, [PaymentStatus::Pending, PaymentStatus::WaitingVerification], true)) {
            throw ValidationException::withMessages([
                'status' => __('messages.payment.not_verifiable'),
            ]);
        }

        return DB::transaction(function () use ($payment, $actor): Payment {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $payment->update([
                'status' => PaymentStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $actor->id,
                'rejection_reason' => null,
            ]);

            $this->penalties->syncStatus($payment->penalty);

            $this->logger->log(
                'payment.verified',
                $payment,
                "Pembayaran {$payment->reference_number} diverifikasi.",
                $actor,
                ['amount' => $payment->amount, 'penalty_id' => $payment->penalty_id],
            );

            return $payment->fresh(['penalty.borrower', 'verifier']);
        });
    }

    public function reject(Payment $payment, User $actor, string $reason): Payment
    {
        $this->assertStaff($payment, $actor);

        if ($payment->status !== PaymentStatus::WaitingVerification) {
            throw ValidationException::withMessages([
                'status' => __('messages.payment.not_rejectable'),
            ]);
        }

        return DB::transaction(function () use ($payment, $actor, $reason): Payment {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            $payment->update([
                'status' => PaymentStatus::Rejected,
                'rejection_reason' => $reason,
                'verified_at' => now(),
                'verified_by' => $actor->id,
            ]);

            $this->logger->log(
                'payment.rejected',
                $payment,
                "Pembayaran {$payment->reference_number} ditolak.",
                $actor,
                ['reason' => $reason],
            );

            return $payment->fresh(['penalty.borrower', 'verifier']);
        });
    }

    private function assertStaff(Payment $payment, User $actor): void
    {
        if ((int) $payment->penalty->organization_id !== (int) $actor->organization_id
            || ! in_array($actor->role?->slug, ['operator', 'organization_admin'], true)
        ) {
            throw new AuthorizationException(__('messages.payment.not_allowed'));
        }
    }
}
