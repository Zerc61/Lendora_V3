@extends('layouts.borrower')

@section('title', $penalty->bap_number)
@section('nav', 'profile')

@section('content')
    <div class="ldr-penalty-detail">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.penalties.index') }}">{{ __('ui.penalty.penalties') }}</a></li>
                <li aria-current="page">{{ $penalty->bap_number }}</li>
            </ol>
        </nav>

        <header class="ldr-penalty-detail__head">
            <div class="ldr-penalty-detail__status">
                <span class="ldr-penalty-detail__bap">{{ $penalty->bap_number }}</span>
                <x-status-badge :status="$penalty->status->value" type="penalty" class="ldr-penalty-detail__badge" />
            </div>

            <h1>{{ __('ui.penalty.bap_title') }}</h1>

            <div class="ldr-penalty-detail__meta">
                <span>{{ __('ui.penalty.asset') }}: {{ $penalty->asset->code ?? '—' }} — {{ $penalty->asset->name ?? '—' }}</span>
                <span>{{ __('ui.penalty.damage') }}: {{ $penalty->damage_description ?? '—' }}</span>
                <span>{{ __('ui.penalty.incident_date') }}: {{ $penalty->incident_at->format('d M Y') }}</span>
                <span>{{ __('ui.penalty.inspector') }}: {{ $penalty->inspector_name ?? '—' }}</span>
            </div>

            @if ($penalty->status !== \App\Enums\PenaltyStatus::Paid)
                <span class="ldr-penalty-detail__due">
                    {{ __('ui.penalty.due') }}: {{ $penalty->due_at->format('d M Y') }}
                </span>
            @endif
        </header>

        <section class="ldr-card">
            <h2>{{ __('ui.penalty.breakdown') }}</h2>
            <div class="ldr-penalty-breakdown">
                <div class="ldr-penalty-breakdown__row">
                    <span>{{ __('ui.penalty.repair_subtotal') }}</span>
                    <span>Rp {{ number_format($breakdown['repair_subtotal'], 0, ',', '.') }}</span>
                </div>
                <div class="ldr-penalty-breakdown__row">
                    <span>{{ __('ui.penalty.subsidy') }}</span>
                    <span class="ldr-text--success">−Rp {{ number_format($breakdown['subsidy_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="ldr-penalty-breakdown__row">
                    <span>{{ __('ui.penalty.admin_fee') }}</span>
                    <span>Rp {{ number_format($breakdown['admin_fee'], 0, ',', '.') }}</span>
                </div>
                <div class="ldr-penalty-breakdown__row ldr-penalty-breakdown__row--total">
                    <span>{{ __('ui.penalty.total') }}</span>
                    <span>Rp {{ number_format($breakdown['total_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </section>

        @if ($payments->isNotEmpty())
            <section class="ldr-card">
                <h2>{{ __('ui.payment.history') }}</h2>
                <table class="ldr-payment-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.payment.method_label') }}</th>
                            <th>{{ __('ui.payment.amount') }}</th>
                            <th>{{ __('ui.payment.status_label') }}</th>
                            <th>{{ __('ui.payment.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>
                                    <a href="{{ route('borrower.payments.show', $payment) }}">
                                        {{ __('ui.payment.method.' . $payment->method->value) }}
                                    </a>
                                </td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td><span class="ldr-badge ldr-badge--{{ $payment->status === \App\Enums\PaymentStatus::Verified ? 'success' : 'warning' }}">{{ __('ui.payment.status.' . $payment->status->value) }}</span></td>
                                <td>{{ $payment->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <section class="ldr-card">
            <h2>{{ __('ui.penalty.recovery_steps') }}</h2>
            <x-borrower.recovery-steps :steps="$recoverySteps" />
        </section>

        @if ($canPay)
            <section class="ldr-card">
                <h2>{{ __('ui.penalty.pay_now') }}</h2>

                <form method="POST" action="{{ route('borrower.penalties.payments.store', $penalty) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="ldr-field">
                        <label class="ldr-label" for="payment-method">{{ __('ui.penalty.pay_method') }}</label>
                        <select id="payment-method" name="method" class="ldr-select" required>
                            @foreach (['va_bni', 'qris', 'sparepart'] as $method)
                                <option value="{{ $method }}" @selected(old('method') === $method)>
                                    {{ $methods[$method]['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('method') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="payment-proof">{{ __('ui.payment.choose_file') }}</label>
                        <input id="payment-proof" class="ldr-input" type="file" name="proof" accept="image/*,application/pdf" required>
                        <p class="ldr-form-hint">{{ __('ui.payment.upload_hint') }}</p>
                        @error('proof') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.penalty.pay_submit') }}</button>
                </form>
            </section>

            @if ($penalty->status === \App\Enums\PenaltyStatus::InstallmentRequested)
                <section class="ldr-card">
                    <h2>{{ __('ui.penalty.installment_title') }}</h2>
                    <p class="ldr-muted">{{ __('ui.penalty.installment_active', ['months' => $penalty->installment_months]) }}</p>
                </section>
            @else
                <section class="ldr-card">
                    <h2>{{ __('ui.penalty.installment_title') }}</h2>
                    <p class="ldr-form-hint">{{ __('ui.penalty.installment_hint', ['max' => $installmentMax]) }}</p>

                    <form method="POST" action="{{ route('borrower.penalties.installment.request', $penalty) }}">
                        @csrf

                        <div class="ldr-field">
                            <label class="ldr-label" for="installment-months">{{ __('ui.penalty.installment_months') }}</label>
                            <select id="installment-months" name="installment_months" class="ldr-select" required>
                                @foreach (range(1, $installmentMax) as $months)
                                    <option value="{{ $months }}" @selected((int) old('installment_months') === $months)>{{ $months }}</option>
                                @endforeach
                            </select>
                            @error('installment_months') <p class="ldr-field__error">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="ldr-btn ldr-btn--secondary">{{ __('ui.penalty.installment_submit') }}</button>
                    </form>
                </section>
            @endif
        @endif

        <div class="ldr-penalty-detail__actions">
            <a href="{{ route('borrower.penalties.document', ['penalty' => $penalty, 'type' => 'bap']) }}" class="ldr-btn ldr-btn--secondary">
                <x-icon name="download" :size="16" />
                {{ __('ui.penalty.print_bap') }}
            </a>
        </div>
    </div>
@endsection