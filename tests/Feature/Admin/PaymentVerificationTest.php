<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $operator;

    private User $borrower;

    private Penalty $penalty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->penalty = $this->penalty();
    }

    public function test_operator_verifies_payment_and_penalty_becomes_paid(): void
    {
        $payment = $this->payment(PaymentStatus::WaitingVerification);

        $this->actingAs($this->operator)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($this->penalty->bap_number)
            ->assertSee(__('ui.payment.verify_title'))
            ->assertSee(__('ui.payment.verify_action'))
            ->assertSee(__('ui.payment.kpi_waiting'))
            ->assertSee(__('ui.payment.kpi_verified'));

        $this->actingAs($this->operator)
            ->post(route('admin.payments.verify', $payment))
            ->assertRedirect();

        $this->assertSame(PaymentStatus::Verified, $payment->fresh()->status);
        $this->assertSame(PenaltyStatus::Paid, $this->penalty->fresh()->status);
    }

    public function test_operator_rejects_payment_with_reason(): void
    {
        $payment = $this->payment(PaymentStatus::WaitingVerification);

        $this->actingAs($this->operator)
            ->post(route('admin.payments.reject', $payment), ['rejection_reason' => 'Bukti tidak terbaca.'])
            ->assertRedirect();

        $payment->refresh();

        $this->assertSame(PaymentStatus::Rejected, $payment->status);
        $this->assertSame('Bukti tidak terbaca.', $payment->rejection_reason);
    }

    public function test_reject_requires_reason(): void
    {
        $payment = $this->payment(PaymentStatus::WaitingVerification);

        $this->actingAs($this->operator)
            ->post(route('admin.payments.reject', $payment), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->fresh()->status);
    }

    public function test_borrower_cannot_verify_payment(): void
    {
        $payment = $this->payment(PaymentStatus::WaitingVerification);

        $this->actingAs($this->borrower)
            ->post(route('admin.payments.verify', $payment))
            ->assertForbidden();
    }

    private function penalty(): Penalty
    {
        return Penalty::create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $this->borrower->id,
            'bap_number' => 'LDR-BAP-2026-9001',
            'incident_at' => now()->subDay(),
            'inspector_name' => 'Budi',
            'damage_description' => 'Kerusakan uji.',
            'repair_subtotal' => 500_000,
            'subsidy_percent' => 0,
            'subsidy_amount' => 0,
            'admin_fee' => 0,
            'total_amount' => 500_000,
            'status' => PenaltyStatus::WaitingPayment,
            'due_at' => now()->addDays(7),
            'created_by' => $this->operator->id,
        ]);
    }

    private function payment(PaymentStatus $status): Payment
    {
        return Payment::create([
            'penalty_id' => $this->penalty->id,
            'borrower_id' => $this->borrower->id,
            'method' => PaymentMethod::Qris,
            'amount' => 500_000,
            'status' => $status,
            'reference_number' => 'QRIS-2026-9001',
        ]);
    }
}
