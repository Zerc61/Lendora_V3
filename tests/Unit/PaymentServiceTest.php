<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private PaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->payments = app(PaymentService::class);
    }

    public function test_verify_full_payment_marks_penalty_paid(): void
    {
        $penalty = $this->penalty(500_000);
        $payment = $this->payment($penalty, 500_000, PaymentStatus::WaitingVerification);

        $verified = $this->payments->verify($payment, $this->operator);

        $this->assertSame(PaymentStatus::Verified, $verified->status);
        $this->assertSame($this->operator->id, $verified->verified_by);
        $this->assertNotNull($verified->verified_at);

        $penalty->refresh();
        $this->assertSame(PenaltyStatus::Paid, $penalty->status);
        $this->assertNotNull($penalty->paid_at);
    }

    public function test_verify_partial_payment_marks_penalty_partial(): void
    {
        $penalty = $this->penalty(500_000);
        $payment = $this->payment($penalty, 200_000, PaymentStatus::WaitingVerification);

        $this->payments->verify($payment, $this->operator);

        $this->assertSame(PenaltyStatus::Partial, $penalty->fresh()->status);
    }

    public function test_verify_rejects_payment_with_invalid_status(): void
    {
        $penalty = $this->penalty(500_000);
        $payment = $this->payment($penalty, 500_000, PaymentStatus::Verified);

        $this->expectException(ValidationException::class);

        $this->payments->verify($payment, $this->operator);
    }

    public function test_reject_records_reason(): void
    {
        $penalty = $this->penalty(500_000);
        $payment = $this->payment($penalty, 500_000, PaymentStatus::WaitingVerification);

        $rejected = $this->payments->reject($payment, $this->operator, 'Bukti tidak terbaca.');

        $this->assertSame(PaymentStatus::Rejected, $rejected->status);
        $this->assertSame('Bukti tidak terbaca.', $rejected->rejection_reason);
        $this->assertSame(PenaltyStatus::WaitingPayment, $penalty->fresh()->status);
    }

    private function penalty(int $total): Penalty
    {
        return Penalty::create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'bap_number' => 'LDR-BAP-2026-'.random_int(1000, 9999),
            'incident_at' => now()->subDay(),
            'inspector_name' => 'Budi',
            'damage_description' => 'Kerusakan uji.',
            'repair_subtotal' => $total,
            'subsidy_percent' => 0,
            'subsidy_amount' => 0,
            'admin_fee' => 0,
            'total_amount' => $total,
            'status' => PenaltyStatus::WaitingPayment,
            'due_at' => now()->addDays(7),
            'created_by' => $this->operator->id,
        ]);
    }

    private function payment(Penalty $penalty, int $amount, PaymentStatus $status): Payment
    {
        return Payment::create([
            'penalty_id' => $penalty->id,
            'borrower_id' => $this->borrower->id,
            'method' => PaymentMethod::Qris,
            'amount' => $amount,
            'status' => $status,
            'reference_number' => 'QRIS-'.random_int(1000, 9999),
        ]);
    }
}
