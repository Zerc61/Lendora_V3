@props(['user', 'activeBooking', 'dueInfo', 'quota', 'compliance'])

<section class="ldr-hero-status" aria-labelledby="hero-status-title">
    <div class="ldr-hero-status__greeting">
        <h1 id="hero-status-title" class="ldr-display">{{ __('ui.borrower.greeting', ['name' => $user->name]) }}</h1>
        <p class="ldr-hero-status__subtitle">{{ __('ui.borrower.portal_subtitle') }}</p>
    </div>

    <div class="ldr-hero-status__badges">
        <span class="ldr-badge ldr-badge--success">
            <x-icon name="check-circle" :size="14" />
            {{ __('ui.borrower.account_verified') }}
        </span>

        @if ($user->nim && $user->study_program)
            <span class="ldr-badge ldr-badge--info">
                <x-icon name="graduation-cap" :size="14" />
                {{ $user->study_program }} ({{ $user->nim }})
            </span>
        @endif

        @if ($user->lab_tier)
            <span class="ldr-badge ldr-badge--primary">
                <x-icon name="star" :size="14" />
                {{ __('ui.borrower.lab_tier', ['tier' => $user->lab_tier]) }}
            </span>
        @endif

        @if (isset($compliance))
            <span class="ldr-badge ldr-badge--success ldr-badge--compliance">
                <x-icon name="award" :size="14" />
                {{ __('ui.borrower.compliance_score', ['score' => $compliance['score'] ?? $compliance]) }}
            </span>
        @endif
    </div>
</section>