@props(['total', 'used', 'remaining'])

<div class="ldr-quota-card">
    <div class="ldr-quota-card__head">
        <div>
            <p class="ldr-muted text-xs">{{ __('ui.profile.quota_semester') }}</p>
            <span class="ldr-compliance-card__score">{{ $remaining }}</span>
            <span>{{ __('ui.profile.quota_hours_left') }}</span>
        </div>
        <span class="ldr-badge ldr-badge--info">
            <x-icon name="timer" :size="16" />
            {{ __('ui.profile.quota_active') }}
        </span>
    </div>

    <div class="ldr-quota-card__progress">
        <div class="ldr-quota-card__progress-bar" style="width: {{ $used > 0 ? min(100, round(($used / $total) * 100)) : 0 }}%"></div>
    </div>

    <div class="ldr-quota-stats">
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">{{ $total }}</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.quota_total') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">{{ $used }}</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.quota_used') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">{{ $remaining }}</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.quota_remaining') }}</span>
        </div>
    </div>
</div>