@extends('layouts.admin')

@section('title', __('ui.payment.verify_title'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">O3 · {{ __('ui.payment.verify_title') }}</span>
            <h1>{{ __('ui.payment.verify_title') }}</h1>
            <p class="ldr-muted">{{ __('ui.payment.verify_description') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="ldr-alert ldr-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.payment.kpi_total') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['total'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.payment.verify_description') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.payment.kpi_waiting') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bell" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['waiting'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.payment.status.waiting_verification') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.payment.kpi_verified') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['verified'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.payment.amount') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.payment.kpi_rejected') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['rejected'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.payment.reject_reason') }}</span>
        </div>
    </div>

    <nav class="ldr-tab-bar" aria-label="{{ __('ui.payment.status_label') }}">
        <a class="ldr-tab {{ ($filters['status'] ?? null) === null ? 'is-active' : '' }}"
           href="{{ route('admin.payments.index') }}">
            {{ __('ui.common.all') }}
        </a>
        @foreach ($statuses as $status)
            <a class="ldr-tab {{ ($filters['status'] ?? null) === $status->value ? 'is-active' : '' }}"
               href="{{ route('admin.payments.index', ['status' => $status->value]) }}">
                {{ __('ui.payment.status.'.$status->value) }}
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.payments.index') }}" class="ldr-filter-bar" role="search">
        @if ($filters['status'] ?? null)
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif

        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="payment-search">{{ __('ui.payment.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="payment-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.payment.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__actions">
            <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="submit">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.payments.index') }}">{{ __('ui.common.reset') }}</a>
        </div>
    </form>

    @if ($payments->isEmpty())
        <x-empty-state
            icon="bar-chart"
            :title="__('ui.payment.empty_title')"
            :description="__('ui.payment.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.payment.reference') }}</th>
                            <th>{{ __('ui.payment.bap') }}</th>
                            <th>{{ __('ui.payment.borrower') }}</th>
                            <th>{{ __('ui.payment.method_label') }}</th>
                            <th class="ldr-table__num">{{ __('ui.payment.amount') }}</th>
                            <th>{{ __('ui.payment.status_label') }}</th>
                            <th>{{ __('ui.payment.proof') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>
                                    <span class="ldr-mono">{{ $payment->reference_number ?: '—' }}</span>
                                    <span class="ldr-table__sub">{{ $payment->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }}</span>
                                </td>
                                <td>
                                    @if ($payment->penalty)
                                        <a class="ldr-table__code ldr-mono" href="{{ route('admin.penalties.show', $payment->penalty) }}">
                                            {{ $payment->penalty->bap_number }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $payment->borrower?->name ?? '—' }}</span>
                                    <span class="ldr-table__sub ldr-mono">{{ $payment->borrower?->nim ?: '—' }}</span>
                                </td>
                                <td>{{ __('ui.payment.method.'.$payment->method->value) }}</td>
                                <td class="ldr-table__num">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td><x-status-badge type="payment" :status="$payment->status->value" /></td>
                                <td>
                                    @if ($payment->proof_path)
                                        <a class="ldr-proof" href="{{ Illuminate\Support\Facades\Storage::url($payment->proof_path) }}" target="_blank" rel="noopener">
                                            <x-icon name="eye" :size="16" />
                                            {{ __('ui.payment.view_proof') }}
                                        </a>
                                    @else
                                        <span class="ldr-muted">{{ __('ui.payment.no_proof') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="ldr-payment-actions">
                                        @if (in_array($payment->status, [\App\Enums\PaymentStatus::Pending, \App\Enums\PaymentStatus::WaitingVerification], true))
                                            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                                @csrf
                                                <button class="ldr-btn ldr-btn--primary ldr-btn--sm" type="submit">
                                                    <x-icon name="check-circle" :size="14" />
                                                    {{ __('ui.payment.verify_action') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($payment->status === \App\Enums\PaymentStatus::WaitingVerification)
                                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="ldr-payment-reject">
                                                @csrf
                                                <label class="ldr-sr-only" for="reject-{{ $payment->id }}">
                                                    {{ __('ui.payment.reject_reason') }}
                                                </label>
                                                <input id="reject-{{ $payment->id }}"
                                                       class="ldr-input"
                                                       type="text"
                                                       name="rejection_reason"
                                                       placeholder="{{ __('ui.payment.reject_reason') }}"
                                                       required
                                                       maxlength="255">
                                                <button class="ldr-btn ldr-btn--ghost ldr-btn--sm" type="submit">
                                                    {{ __('ui.payment.reject_action') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($payment->status === \App\Enums\PaymentStatus::Rejected && $payment->rejection_reason)
                                            <span class="ldr-muted">{{ $payment->rejection_reason }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$payments" />
        </div>
    @endif
@endsection
