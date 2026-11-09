@extends('layouts.borrower')

@section('title', $booking->code)
@section('nav', 'bookings')

@section('content')
    <div class="ldr-booking-detail">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.bookings.index') }}">{{ __('ui.nav.bookings') }}</a></li>
                <li aria-current="page">{{ $booking->code }}</li>
            </ol>
        </nav>

        <header class="ldr-booking-detail__head">
            <div class="ldr-booking-detail__meta">
                <span class="ldr-booking-detail__code">{{ $booking->code }}</span>
                <x-status-badge type="booking" :status="$booking->status->value" class="ldr-booking-detail__status" />
            </div>

            <h1 class="ldr-booking-detail__title">{{ __('ui.booking.detail') }}</h1>

            <p class="ldr-booking-detail__purpose">{{ $booking->purpose }}</p>

            <div class="ldr-booking-detail__info">
                <span>
                    <x-icon name="calendar" :size="15" />
                    {{ $booking->requested_start_date->locale('id')->translatedFormat('d M Y') }} — {{ $booking->requested_end_date->locale('id')->translatedFormat('d M Y') }}
                </span>
                @if ($booking->due_at)
                    <span>
                        <x-icon name="clock" :size="15" />
                        {{ __('ui.booking.due_at') }}: {{ $booking->due_at->locale('id')->translatedFormat('d M Y H:i') }}
                    </span>
                @endif
            </div>
        </header>

        @if ($booking->status === \App\Enums\BookingStatus::Rejected)
            <div class="ldr-alert ldr-alert--danger">
                <x-icon name="alert-circle" :size="18" />
                <div>
                    <strong>{{ __('ui.booking.reject_title') }}</strong>
                    <p>{{ $booking->rejection_reason ?? __('ui.booking.reject_help') }}</p>
                </div>
            </div>
        @endif

        <div class="ldr-booking-detail__assets">
            <h3>{{ __('ui.booking.items_summary') }}</h3>
            <ul class="ldr-booking-items">
                @foreach ($booking->items as $item)
                    <li class="ldr-booking-items__row">
                        <div class="ldr-booking-items__main">
                            <span class="ldr-booking-items__name">{{ $item->asset->name }}</span>
                            <p class="ldr-muted">{{ $item->asset->code }}</p>
                        </div>
                        <span class="ldr-badge">{{ __('ui.item_status.' . $item->status->value) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="ldr-detail-actions">
            @if ($booking->status === \App\Enums\BookingStatus::Active)
                <a href="{{ route('borrower.bookings.document', ['booking' => $booking, 'type' => 'letter']) }}" class="ldr-btn ldr-btn--secondary">
                    <x-icon name="download" :size="16" />
                    {{ __('ui.booking.document_letter') }}
                </a>
                <a href="{{ route('borrower.bookings.extension.form', $booking) }}" class="ldr-btn ldr-btn--primary">
                    <x-icon name="clock" :size="16" />
                    {{ __('ui.booking.extension') }}
                </a>
            @endif

            <a href="{{ route('borrower.bookings.index') }}" class="ldr-btn ldr-btn--ghost">
                {{ __('ui.common.back') }}
            </a>
        </div>

        @php
            $pendingExtension = $booking->extensions->firstWhere('status', \App\Enums\BookingExtensionStatus::Pending);
        @endphp

        @if ($pendingExtension)
            <div class="ldr-card">
                <h2>{{ __('ui.booking.extension') }}</h2>
                <p class="ldr-muted">
                    {{ __('ui.booking.extension_pending', ['date' => $pendingExtension->requested_due_at->translatedFormat('d M Y')]) }}
                </p>
                @if ($pendingExtension->reason)
                    <p>{{ $pendingExtension->reason }}</p>
                @endif
                <form method="POST" action="{{ route('borrower.bookings.extensions.cancel', [$booking, $pendingExtension]) }}">
                    @csrf
                    <button type="submit" class="ldr-btn ldr-btn--sm ldr-btn--danger">{{ __('ui.common.cancel') }}</button>
                </form>
            </div>
        @endif
    </div>
@endsection