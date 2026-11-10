@props(['activeLoans', 'penalties', 'quotaRemaining'])

<div class="ldr-stat-strip" aria-label="{{ __('ui.borrower.metrics_strip') }}">
    <a class="ldr-stat-strip__item" href="{{ route('borrower.bookings.index', ['status' => 'active']) }}">
        <span class="ldr-stat-strip__icon ldr-stat-strip__icon--info">
            <x-icon name="layers" :size="20" />
        </span>
        <span class="ldr-stat-strip__value">{{ $activeLoans }}</span>
        <span class="ldr-stat-strip__label">{{ __('ui.booking_status.active') }}</span>
    </a>

    <a class="ldr-stat-strip__item" href="{{ route('borrower.penalties.index') }}">
        <span class="ldr-stat-strip__icon ldr-stat-strip__icon--danger">
            <x-icon name="alert-triangle" :size="20" />
        </span>
        <span class="ldr-stat-strip__value">{{ $penalties }}</span>
        <span class="ldr-stat-strip__label">{{ __('ui.penalty.penalties') }}</span>
    </a>

    <div class="ldr-stat-strip__item ldr-stat-strip__item--quota">
        <span class="ldr-stat-strip__icon ldr-stat-strip__icon--info">
            <x-icon name="timer" :size="20" />
        </span>
        <span class="ldr-stat-strip__value">{{ $quotaRemaining ?? '—' }}</span>
        <span class="ldr-stat-strip__label">{{ __('ui.borrower.quota_remaining') }}</span>
    </div>
</div>