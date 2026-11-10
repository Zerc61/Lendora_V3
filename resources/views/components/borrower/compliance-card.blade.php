@props(['score', 'tier'])

<div class="ldr-compliance-card">
    <div class="ldr-compliance-card__head">
        <div>
            <p class="ldr-muted text-xs">{{ __('ui.profile.compliance_score') }}</p>
            <span class="ldr-compliance-card__score">{{ $score }}</span>
            <span class="ldr-compliance-card__tier ldr-compliance-card__tier--{{ strtolower(str_replace('+', '', $tier ?? 'D')) }}">
                {{ $tier }}
            </span>
        </div>
        <span class="ldr-badge ldr-badge--success">
            <x-icon name="shield-check" :size="16" />
            {{ __('ui.profile.account_good') }}
        </span>
    </div>

    <div class="ldr-compliance-stats">
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">100%</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.on_time') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">0</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.damages') }}</span>
        </div>
        <div class="ldr-compliance-stat">
            <span class="ldr-compliance-stat__value">0</span>
            <span class="ldr-compliance-stat__label">{{ __('ui.profile.violations') }}</span>
        </div>
    </div>
</div>