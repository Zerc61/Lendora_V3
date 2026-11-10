@props(['booking', 'dueInfo', 'stepperData'])

@php
    $activeItem = $booking?->items->first();
    $activeAsset = $activeItem?->asset;
    $activePhoto = $activeAsset?->photos->first();
@endphp

@if ($booking)
    <section class="ldr-active-loan" aria-labelledby="active-loan-title">
        <div class="ldr-active-loan__head">
            <span class="ldr-active-loan__label">
                <x-icon name="zap" :size="16" />
                {{ __('ui.borrower.active_loan') }}
            </span>
            <x-status-badge type="booking" :status="$booking->status->value" />
            <span class="ldr-mono ldr-muted">{{ $booking->code }}</span>
        </div>

        <div class="ldr-active-loan__body">
            <span class="ldr-active-loan__media">
                @if ($activePhoto)
                    <img src="{{ Illuminate\Support\Facades\Storage::url($activePhoto->path) }}"
                         alt="{{ $activeAsset->name }}"
                         loading="lazy"
                         width="88"
                         height="88">
                @else
                    <x-icon name="camera" :size="28" />
                @endif
            </span>

            <div class="ldr-active-loan__info">
                <h2 id="active-loan-title">{{ $activeAsset?->name ?? __('ui.booking.assets') }}</h2>

                <div class="ldr-active-loan__meta">
                    <span>
                        <x-icon name="calendar" :size="15" />
                        {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M') }}
                        –
                        {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
                    </span>

                    @if ($activeAsset?->code)
                        <span class="ldr-mono">{{ $activeAsset->code }}</span>
                    @endif

                    @if ($activeAsset?->location)
                        <span>
                            <x-icon name="package" :size="15" />
                            {{ $activeAsset->location }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <x-borrower.booking-stepper :steps="$stepperData" :current-step="$booking->status" :compact="true" />

        <div class="ldr-active-loan__footer">
            @if ($dueInfo !== null)
                <span class="ldr-active-loan__due {{ $dueInfo['is_overdue'] ? 'is-overdue' : '' }}">
                    <x-icon name="alert-circle" :size="16" />
                    {{ __('ui.borrower.due') }}:
                    {{ $dueInfo['due_at']->locale('id')->translatedFormat('d M Y H:i') }}
                    @if ($dueInfo['is_overdue'])
                        · {{ __('ui.borrower.overdue') }}
                    @else
                        · {{ __('ui.borrower.days_left', ['count' => $dueInfo['days_left']]) }}
                    @endif
                </span>
            @else
                <span class="ldr-muted">{{ __('ui.booking.due_at') }}: —</span>
            @endif

            <div class="ldr-active-loan__actions">
                <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.bookings.document', ['booking' => $booking, 'type' => 'letter']) }}">
                    <x-icon name="download" :size="16" />
                    {{ __('ui.booking.document_letter') }}
                </a>
                <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.bookings.extension.form', $booking) }}">
                    <x-icon name="clock" :size="16" />
                    {{ __('ui.booking.extension') }}
                </a>
                <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.bookings.show', $booking) }}">
                    {{ __('ui.booking.detail_action') }}
                    <x-icon name="arrow-right" :size="16" />
                </a>
            </div>
        </div>
    </section>
@else
    <x-empty-state
        icon="package"
        :title="__('ui.borrower.no_active_title')"
        :description="__('ui.borrower.no_active_description')"
    >
        <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
            {{ __('ui.nav.catalog') }}
        </a>
    </x-empty-state>
@endif