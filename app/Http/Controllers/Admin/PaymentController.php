<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectPaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'status' => PaymentStatus::tryFrom((string) $request->query('status'))?->value,
        ];

        $payments = Payment::query()
            ->with(['penalty.borrower', 'borrower', 'verifier'])
            ->whereHas('penalty', fn ($query) => $query->where('organization_id', $organizationId))
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('penalty', fn ($query) => $query->where('bap_number', 'like', "%{$search}%"))
                        ->orWhereHas('borrower', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'], fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Payment::query()
            ->whereHas('penalty', fn ($query) => $query->where('organization_id', $organizationId))
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.payments.index', [
            'payments' => $payments,
            'filters' => $filters,
            'statuses' => PaymentStatus::cases(),
            'summary' => [
                'total' => (int) $counts->sum(),
                'waiting' => (int) ($counts[PaymentStatus::WaitingVerification->value] ?? 0),
                'verified' => (int) ($counts[PaymentStatus::Verified->value] ?? 0),
                'rejected' => (int) ($counts[PaymentStatus::Rejected->value] ?? 0),
            ],
        ]);
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $this->assertOrganization($request, $payment);

        $this->payments->verify($payment, $request->user());

        return back()->with('status', __('messages.payment.verified'));
    }

    public function reject(RejectPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->payments->reject($payment, $request->user(), (string) $request->input('rejection_reason'));

        return back()->with('status', __('messages.payment.rejected'));
    }

    private function assertOrganization(Request $request, Payment $payment): void
    {
        abort_unless($payment->penalty->organization_id === $request->user()->organization_id, 404);
    }
}
