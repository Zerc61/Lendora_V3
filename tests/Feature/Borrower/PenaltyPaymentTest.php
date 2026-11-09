<?php

namespace Tests\Feature\Borrower;

use App\Enums\PenaltyStatus;
use App\Models\Organization;
use App\Models\Penalty;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenaltyPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->organization = Organization::where('code', 'LDR')->firstOrFail();
        $this->borrower = User::where('email', 'borrower1@lendora.test')->firstOrFail();
        $this->borrower->update(['nim' => '123456789']);
    }

    public function test_borrower_submits_payment_and_it_waits_for_verification(): void
    {
        Storage::fake('public');
        $penalty = $this->penalty();

        $response = $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.payments.store', $penalty), [
                'method' => 'va_bni',
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

        $payment = $penalty->payments()->firstOrFail();

        $response->assertRedirect(route('borrower.payments.show', $payment));

        $this->assertSame(250_000, $payment->amount);
        $this->assertSame('waiting_verification', $payment->status->value);
        $this->assertSame('988-0012-123456789', $payment->reference_number);
        Storage::disk('public')->assertExists($payment->proof_path);
    }

    public function test_payment_rejects_installment_method_and_missing_proof(): void
    {
        $penalty = $this->penalty();

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.payments.store', $penalty), [
                'method' => 'installment',
            ])
            ->assertSessionHasErrors(['method', 'proof']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_borrower_requests_installment_within_limit(): void
    {
        $penalty = $this->penalty();

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.installment.request', $penalty), [
                'installment_months' => 3,
            ])
            ->assertRedirect(route('borrower.penalties.show', $penalty));

        $penalty->refresh();

        $this->assertSame(PenaltyStatus::InstallmentRequested, $penalty->status);
        $this->assertSame(3, $penalty->installment_months);
    }

    public function test_installment_above_setting_limit_is_rejected(): void
    {
        $penalty = $this->penalty();

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.installment.request', $penalty), [
                'installment_months' => 5,
            ])
            ->assertSessionHasErrors('installment_months');

        $this->assertSame(PenaltyStatus::WaitingPayment, $penalty->fresh()->status);
    }

    public function test_borrower_cannot_pay_another_borrowers_penalty(): void
    {
        $other = User::where('email', 'borrower2@lendora.test')->firstOrFail();
        $penalty = $this->penalty($other);

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.payments.store', $penalty), [
                'method' => 'qris',
            ])
            ->assertForbidden();

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.installment.request', $penalty), [
                'installment_months' => 2,
            ])
            ->assertForbidden();
    }

    public function test_penalty_page_shows_payment_and_installment_forms(): void
    {
        $penalty = $this->penalty();

        $this->actingAs($this->borrower)
            ->get(route('borrower.penalties.show', $penalty))
            ->assertOk()
            ->assertSee(route('borrower.penalties.payments.store', $penalty), false)
            ->assertSee(route('borrower.penalties.installment.request', $penalty), false)
            ->assertSee('Kirim Pembayaran')
            ->assertSee('Ajukan Cicilan');
    }

    public function test_suspended_borrower_can_still_open_and_pay_penalty(): void
    {
        $penalty = $this->penalty();
        $this->borrower->update(['status' => 'suspended']);

        $this->actingAs($this->borrower)
            ->get(route('borrower.penalties.show', $penalty))
            ->assertOk()
            ->assertSee(route('borrower.penalties.payments.store', $penalty), false);

        $this->actingAs($this->borrower)
            ->post(route('borrower.penalties.installment.request', $penalty), [
                'installment_months' => 2,
            ])
            ->assertRedirect(route('borrower.penalties.show', $penalty));

        $this->assertSame(PenaltyStatus::InstallmentRequested, $penalty->fresh()->status);
    }

    private function penalty(?User $borrower = null): Penalty
    {
        return Penalty::create([
            'organization_id' => $this->organization->id,
            'borrower_id' => ($borrower ?? $this->borrower)->id,
            'bap_number' => 'LDR-BAP-TEST-'.uniqid(),
            'incident_at' => now(),
            'inspector_name' => 'Budi Santoso',
            'damage_description' => 'Cacat sensor.',
            'repair_subtotal' => 300_000,
            'subsidy_percent' => 10,
            'subsidy_amount' => 50_000,
            'admin_fee' => 0,
            'total_amount' => 250_000,
            'status' => PenaltyStatus::WaitingPayment,
            'due_at' => now()->addDays(4),
        ]);
    }
}
