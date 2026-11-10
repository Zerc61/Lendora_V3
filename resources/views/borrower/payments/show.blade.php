@extends('layouts.borrower')

@section('title', __('ui.payment.title'))
@section('nav', 'profile')

@section('content')
    <div class="ldr-penalty-detail">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.penalties.index') }}">{{ __('ui.penalty.penalties') }}</a></li>
                <li><a href="{{ route('borrower.penalties.show', $penalty) }}">{{ $penalty->bap_number }}</a></li>
                <li aria-current="page">{{ __('ui.payment.title') }}</li>
            </ol>
        </nav>

        <header class="ldr-penalty-detail__head">
            <div class="ldr-penalty-detail__status">
                <span class="ldr-penalty-detail__bap">{{ $penalty->bap_number }}</span>
                <x-status-badge :status="$payment->status->value" type="payment" />
            </div>
            <h1>{{ __('ui.payment.title') }}</h1>
        </header>

        <section class="ldr-card">
            <dl class="ldr-spec-list">
                <div>
                    <dt>{{ __('ui.payment.method_label') }}</dt>
                    <dd>{{ __('ui.payment.method.' . $payment->method->value) }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.payment.amount') }}</dt>
                    <dd>Rp {{ number_format($payment->amount, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.payment.status_label') }}</dt>
                    <dd>{{ __('ui.payment.status.' . $payment->status->value) }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.payment.date') }}</dt>
                    <dd>{{ $payment->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </section>

        @if ($payment->proof_path)
            <section class="ldr-card">
                <h2>{{ __('ui.payment.proof') }}</h2>
                <a class="ldr-btn ldr-btn--secondary" href="{{ \Illuminate\Support\Facades\Storage::url($payment->proof_path) }}" target="_blank" rel="noopener">
                    {{ __('ui.payment.proof') }}
                </a>
            </section>
        @endif

        @if (in_array($payment->status, [\App\Enums\PaymentStatus::Pending, \App\Enums\PaymentStatus::WaitingVerification], true))
            <form method="POST" action="{{ route('borrower.payments.proof', $payment) }}" enctype="multipart/form-data" class="ldr-card">
                @csrf

                <h2>{{ __('ui.payment.upload_proof') }}</h2>
                <p class="ldr-form-hint">{{ __('ui.payment.upload_hint') }}</p>

                <div class="ldr-field">
                    <label class="ldr-label" for="proof">{{ __('ui.payment.choose_file') }}</label>
                    <input id="proof" class="ldr-input" type="file" name="proof" accept="image/*,application/pdf" required>
                    @error('proof') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.payment.upload_proof') }}</button>
                </div>
            </form>
        @endif
    </div>
@endsection
