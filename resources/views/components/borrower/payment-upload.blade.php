@props([])

<div class="ldr-payment-upload">
    <svg class="ldr-payment-upload__icon" aria-hidden="true">
        <path d="M12 2v16M4.934 4.934l14.132 14.132" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </svg>
    <p class="ldr-payment-upload__label">{{ __('ui.payment.upload_proof') }}</p>
    <p class="ldr-payment-upload__hint">{{ __('ui.payment.upload_hint') }}</p>
    <input type="file" class="hidden" data-file-input id="payment-proof" accept="image/*,application/pdf">
    <label for="payment-proof" class="ldr-btn ldr-btn--primary ldr-btn--block mt-4">
        {{ __('ui.payment.choose_file') }}
    </label>
    <span id="payment-proof-name" class="ldr-payment-upload__file-name"></span>
</div>