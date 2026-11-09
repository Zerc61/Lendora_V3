<?php

namespace App\Http\Controllers\Borrower;

use App\Enums\PenaltyStatus;
use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Services\PaymentService;
use App\Services\PenaltyService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenaltyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $penalties = Penalty::where('borrower_id', $user->id)
            ->with('asset')
            ->latest()
            ->paginate(10);

        $summary = app(PenaltyService::class)->summaryFor($user);

        return view('borrower.penalties.index', [
            'penalties' => $penalties,
            'summary' => $summary,
            'compliance' => app(\App\Services\BorrowerScoreService::class)->summary($user),
        ]);
    }

    public function show(Request $request, Penalty $penalty): View
    {
        abort_unless($penalty->borrower_id === $request->user()->id, 403);
        $settings = app(SettingService::class);

        $breakdown = [
            'repair_subtotal' => $penalty->repair_subtotal,
            'subsidy_amount' => $penalty->subsidy_amount,
            'admin_fee' => $penalty->admin_fee,
            'total_amount' => $penalty->total_amount,
        ];

        $payments = $penalty->payments()->latest()->get();
        $paymentSummary = app(PaymentService::class)->summaryFor($penalty);

        $canPay = in_array($penalty->status, [
            PenaltyStatus::WaitingPayment,
            PenaltyStatus::Partial,
            PenaltyStatus::Overdue,
            PenaltyStatus::InstallmentRequested,
        ], true);

        return view('borrower.penalties.show', [
            'penalty' => $penalty,
            'breakdown' => $breakdown,
            'payments' => $payments,
            'paymentSummary' => $paymentSummary,
            'methods' => [
                'va_bni' => ['label' => 'Virtual Account BNI', 'instant' => true],
                'qris' => ['label' => 'QRIS / E-Wallet', 'instant' => false],
                'sparepart' => ['label' => 'Penggantian Sparepart', 'instant' => false],
                'installment' => ['label' => 'Cicilan / Keringanan', 'instant' => false],
            ],
            'vaNumber' => $penalty->borrower->nim ? '988-0012-' . $penalty->borrower->nim : null,
            'recoverySteps' => [
                1 => ['title' => 'Verifikasi Pembayaran', 'description' => 'Operator memverifikasi bukti pembayaran.'],
                2 => ['title' => $settings->get('recovery_step_2_title', 'Workshop SOP Lab Outdoor (1 Hari)'), 'description' => 'Ikuti workshop pemulihan akses.'],
                3 => ['title' => $settings->get('recovery_step_3_title', 'Otorisasi Pemulihan oleh Kaprodi'), 'description' => 'Dapatkan persetujuan Kaprodi.'],
            ],
            'paymentSettings' => [
                'due_days' => (int) $settings->get('penalty_due_days', 4),
            ],
            'installmentMax' => (int) $settings->get('installment_max', 3),
            'canPay' => $canPay,
        ]);
    }

    public function document(Request $request, Penalty $penalty, string $type): View
    {
        abort_unless($penalty->borrower_id === $request->user()->id, 403);

        $breakdown = [
            'repair_subtotal' => $penalty->repair_subtotal,
            'subsidy_amount' => $penalty->subsidy_amount,
            'admin_fee' => $penalty->admin_fee,
            'total_amount' => $penalty->total_amount,
        ];

        return view('borrower.penalties.document', [
            'penalty' => $penalty,
            'breakdown' => $breakdown,
            'type' => $type,
        ]);
    }

    public function submitProof(Request $request, \App\Models\Payment $payment): \Illuminate\Http\RedirectResponse
    {
        abort_unless($payment->penalty->borrower_id === $request->user()->id, 403);

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $payment->update([
            'proof_path' => $request->file('proof')->store('penalties/proofs', 'public'),
            'status' => \App\Enums\PaymentStatus::WaitingVerification,
        ]);

        return back()->with('status', __('messages.payment.proof_submitted'));
    }
}