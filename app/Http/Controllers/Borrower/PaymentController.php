<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PenaltyStatus;
use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Payment;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function store(Request $request, Penalty $penalty): RedirectResponse
    {
        abort_unless($penalty->borrower_id === $request->user()->id, 403);

        $this->ensurePayable($penalty);

        $request->validate([
            'method' => ['required', 'in:va_bni,qris,sparepart'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof')->store('penalties/proofs', 'public');
        $method = PaymentMethod::from((string) $request->input('method'));

        $payment = $penalty->payments()->create([
            'borrower_id' => $request->user()->id,
            'method' => $method,
            'amount' => $penalty->remainingAmount(),
            'status' => PaymentStatus::WaitingVerification,
            'reference_number' => $this->referenceFor($request, $method),
            'proof_path' => $path,
        ]);

        return redirect()
            ->route('borrower.payments.show', $payment)
            ->with('status', __('messages.payment.proof_submitted'));
    }

    public function installment(Request $request, Penalty $penalty): RedirectResponse
    {
        abort_unless($penalty->borrower_id === $request->user()->id, 403);

        $this->ensurePayable($penalty);

        if ($penalty->status === PenaltyStatus::InstallmentRequested) {
            throw ValidationException::withMessages([
                'installment_months' => __('messages.payment.installment_pending'),
            ]);
        }

        $maxMonths = max(1, (int) app(SettingService::class)->get('installment_max', 3));

        $request->validate([
            'installment_months' => ['required', 'integer', 'min:1', 'max:'.$maxMonths],
        ]);

        $penalty->update([
            'installment_months' => (int) $request->input('installment_months'),
            'status' => PenaltyStatus::InstallmentRequested,
        ]);

        return redirect()
            ->route('borrower.penalties.show', $penalty)
            ->with('status', __('messages.payment.installment_requested'));
    }

    public function show(Request $request, Payment $payment): View
    {
        abort_unless($payment->penalty->borrower_id === $request->user()->id, 403);

        return view('borrower.payments.show', [
            'payment' => $payment,
            'penalty' => $payment->penalty,
        ]);
    }

    private function ensurePayable(Penalty $penalty): void
    {
        if (! in_array($penalty->status, [
            PenaltyStatus::WaitingPayment,
            PenaltyStatus::Partial,
            PenaltyStatus::Overdue,
            PenaltyStatus::InstallmentRequested,
        ], true)) {
            throw ValidationException::withMessages([
                'method' => __('messages.payment.not_payable'),
            ]);
        }

        if ($penalty->remainingAmount() <= 0) {
            throw ValidationException::withMessages([
                'method' => __('messages.payment.already_paid'),
            ]);
        }
    }

    private function referenceFor(Request $request, PaymentMethod $method): ?string
    {
        if ($method !== PaymentMethod::VaBni) {
            return null;
        }

        $nim = (string) ($request->user()->nim ?? '');

        if ($nim === '') {
            return null;
        }

        $prefix = (string) app(SettingService::class)->get('va_prefix', '988-0012');

        return $prefix.'-'.$nim;
    }
}
