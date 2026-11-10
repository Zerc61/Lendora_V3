@props(['total', 'amount'])

<div class="ldr-sanction-card">
    <div class="ldr-sanction-card__head">
        <div>
            <p class="ldr-muted text-xs">{{ __('ui.penalty.penalties') }}</p>
            <span class="ldr-sanction-card__total">Rp {{ number_format($amount, 0, ',', '.') }}</span>
        </div>
        <span class="ldr-badge ldr-badge--{{ $total > 0 ? 'danger' : 'success' }}">
            <x-icon name="alert-triangle" :size="16" />
            {{ $total > 0 ? __('ui.penalty.status.waiting_payment') : __('ui.penalty.status.paid') }}
        </span>
    </div>

    <div class="ldr-sanction-stats">
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">{{ $total }}</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.penalty.active') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">0</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.penalty.paid') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">4 hari</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.penalty.due_in') }}</span>
        </div>
    </div>
</div>