@extends('layouts.borrower')

@section('title', $booking->code)
@section('nav', 'bookings')

@section('content')
    @php
        use App\Enums\BookingStatus;
        use App\Enums\BookingExtensionStatus;

        $status = $booking->status;
        $steps = [
            ['status' => BookingStatus::Pending, 'label' => __('ui.booking.timeline_steps.submitted')],
            ['status' => BookingStatus::Approved, 'label' => __('ui.booking.timeline_steps.approved')],
            ['status' => BookingStatus::Active, 'label' => __('ui.booking.timeline_steps.picked_up')],
            ['status' => BookingStatus::Returned, 'label' => __('ui.booking.timeline_steps.returned')],
        ];
        $stepIndex = match ($status) {
            BookingStatus::Pending => 0,
            BookingStatus::Approved => 1,
            BookingStatus::Active => 2,
            BookingStatus::Returned => 3,
            default => -1,
        };
        $pendingExtension = $booking->extensions->firstWhere('status', BookingExtensionStatus::Pending);
    @endphp

    <div class="ldr-loan-page">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.bookings.index') }}">{{ __('ui.nav.bookings') }}</a></li>
                <li aria-current="page">{{ $booking->code }}</li>
            </ol>
        </nav>

        <header class="ldr-loan-summary">
            <div class="ldr-loan-summary__identity">
                <span class="ldr-loan-summary__icon"><x-icon name="camera" :size="22" /></span>
                <div>
                    <div class="ldr-loan-summary__topline">
                        <h1 class="ldr-mono ldr-loan-code">{{ $booking->code }}</h1>
                        <x-status-badge type="booking" :status="$status->value" />
                    </div>
                    <p>{{ $booking->items->pluck('asset.name')->filter()->join(', ') }}</p>
                </div>
            </div>
            <div class="ldr-loan-due">
                <x-icon name="calendar" :size="18" />
                <div>
                    <span>{{ __('ui.booking.period') }}</span>
                    <strong>{{ $booking->requested_start_date->locale('id')->translatedFormat('d M Y') }} — {{ $booking->requested_end_date->locale('id')->translatedFormat('d M Y') }}</strong>
                    @if ($booking->due_at)
                        <small>{{ __('ui.booking.due_at') }}: {{ $booking->due_at->locale('id')->translatedFormat('d M Y H:i') }}</small>
                    @endif
                </div>
            </div>
        </header>

        <div class="ldr-loan-actions">
            @if ($status === BookingStatus::Active)
                <a href="{{ route('borrower.bookings.return-pass', $booking) }}" class="ldr-btn ldr-btn--primary"><x-icon name="clipboard-check" :size="16" /> {{ __('ui.loan_detail.return_pass') }}</a>
                <a href="{{ route('borrower.bookings.extension.form', $booking) }}" class="ldr-btn ldr-btn--secondary"><x-icon name="calendar" :size="16" /> {{ __('ui.booking.extension') }}</a>
                <a href="{{ route('borrower.bookings.document', ['booking' => $booking, 'type' => 'letter']) }}" class="ldr-btn ldr-btn--secondary"><x-icon name="clipboard-list" :size="16" /> {{ __('ui.booking.document_letter') }}</a>
            @endif
            <a href="{{ route('borrower.bookings.index') }}" class="ldr-btn ldr-btn--ghost"><x-icon name="chevron-left" :size="16" /> {{ __('ui.common.back') }}</a>
        </div>

        <div class="ldr-loan-grid">
            <div class="ldr-loan-main">
                <section class="ldr-loan-panel">
                    <div class="ldr-loan-panel__heading">
                        <div><span class="ldr-request-eyebrow">{{ __('ui.booking.code') }}</span><h2>{{ __('ui.loan_detail.timeline_title') }}</h2></div>
                        @if ($stepIndex >= 0)
                            <span class="ldr-loan-step-count">{{ __('ui.loan_detail.step_count', ['current' => $stepIndex + 1, 'total' => count($steps)]) }}</span>
                        @endif
                    </div>

                    @if (in_array($status, [BookingStatus::Rejected, BookingStatus::Cancelled], true))
                        <div class="ldr-loan-terminal-state {{ $status === BookingStatus::Rejected ? 'is-danger' : '' }}">
                            <x-icon :name="$status === BookingStatus::Rejected ? 'alert-circle' : 'x'" :size="20" />
                            <div>
                                <strong>{{ __('ui.booking_status.' . $status->value) }}</strong>
                                @if ($status === BookingStatus::Rejected && $booking->rejection_reason)
                                    <p>{{ $booking->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <ol class="ldr-loan-timeline">
                            @foreach ($steps as $index => $step)
                                @php
                                    $event = collect($timeline)->firstWhere('status', $step['status']->value);
                                    $complete = $stepIndex > $index;
                                    $current = $stepIndex === $index;
                                @endphp
                                <li class="ldr-loan-timeline__item {{ $complete ? 'is-complete' : '' }} {{ $current ? 'is-current' : '' }}">
                                    <span class="ldr-loan-timeline__marker" aria-hidden="true">
                                        @if ($complete)<x-icon name="check-circle" :size="17" />@else{{ $index + 1 }}@endif
                                    </span>
                                    <div class="ldr-loan-timeline__content">
                                        <div class="ldr-loan-timeline__heading">
                                            <strong>{{ $step['label'] }}</strong>
                                            @if ($event && $event['time'])
                                                <time datetime="{{ $event['time']->toIso8601String() }}">{{ $event['time']->locale('id')->translatedFormat('d M Y, H:i') }}</time>
                                            @endif
                                        </div>
                                        @if ($event)
                                            <p>{{ $event['note'] }}@if ($event['actor']) · {{ $event['actor'] }}@endif</p>
                                        @elseif ($current)
                                            <p>{{ __('ui.booking_status.' . $status->value) }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <section class="ldr-loan-panel">
                    <div class="ldr-loan-panel__heading">
                        <div><span class="ldr-request-eyebrow">{{ __('ui.loan_detail.physical_units') }}</span><h2>{{ __('ui.booking.items_summary') }}</h2></div>
                        <span class="ldr-loan-step-count">{{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}</span>
                    </div>
                    <ul class="ldr-loan-assets">
                        @foreach ($booking->items as $item)
                            @php $assetPhoto = $item->asset->photos->first(); @endphp
                            <li>
                                <a class="ldr-loan-asset__image" href="{{ route('borrower.catalog.show', $item->asset) }}" aria-label="{{ __('ui.loan_detail.view_asset', ['name' => $item->asset->name]) }}">
                                    @if ($assetPhoto)
                                        <img src="{{ Illuminate\Support\Facades\Storage::url($assetPhoto->path) }}" alt="" width="64" height="64" loading="lazy">
                                    @else
                                        <x-icon name="camera" :size="22" />
                                    @endif
                                </a>
                                <div class="ldr-loan-asset__info">
                                    <a href="{{ route('borrower.catalog.show', $item->asset) }}">{{ $item->asset->name }}</a>
                                    <span class="ldr-mono">{{ $item->asset->code }}</span>
                                </div>
                                <x-status-badge type="item" :status="$item->status->value" />
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ldr-loan-panel ldr-loan-purpose">
                    <span class="ldr-request-eyebrow">{{ __('ui.booking.purpose') }}</span>
                    <p>{{ $booking->purpose }}</p>
                </section>
            </div>

            <aside class="ldr-loan-aside">
                <section class="ldr-loan-panel">
                    <span class="ldr-request-eyebrow">{{ __('ui.loan_detail.transaction_summary') }}</span>
                    <h2>{{ __('ui.loan_detail.schedule_title') }}</h2>
                    <dl class="ldr-loan-metadata">
                        <div><dt>{{ __('ui.booking.start') }}</dt><dd>{{ $booking->requested_start_date->locale('id')->translatedFormat('d M Y') }}</dd></div>
                        <div><dt>{{ __('ui.booking.end') }}</dt><dd>{{ $booking->requested_end_date->locale('id')->translatedFormat('d M Y') }}</dd></div>
                    </dl>
                </section>

                @if ($pendingExtension)
                    <section class="ldr-loan-panel">
                        <span class="ldr-request-eyebrow">{{ __('ui.booking.extension') }}</span>
                        <h2>{{ __('ui.booking.extension_pending', ['date' => $pendingExtension->requested_due_at->translatedFormat('d M Y')]) }}</h2>
                        @if ($pendingExtension->reason)<p>{{ $pendingExtension->reason }}</p>@endif
                        <form method="POST" action="{{ route('borrower.bookings.extensions.cancel', [$booking, $pendingExtension]) }}" class="ldr-loan-extension-cancel">
                            @csrf
                            <button type="submit" class="ldr-btn ldr-btn--secondary">{{ __('ui.common.cancel') }}</button>
                        </form>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
