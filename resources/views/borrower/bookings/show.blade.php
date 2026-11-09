@extends('layouts.borrower')

@section('title', $booking->code)
@section('nav', 'bookings')

@section('content')
    <div class="ldr-booking-detail">
    <a class="ldr-back-link" href="{{ route('borrower.bookings.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.booking.back_to_bookings') }}
    </a>

    <header class="ldr-booking-detail__head">
        <div class="ldr-booking-card__head">
            <span class="ldr-mono ldr-booking-detail__code">{{ $booking->code }}</span>
            <x-status-badge type="booking" :status="$booking->status->value" />
        </div>

        <p class="ldr-muted">
            {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
            –
            {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
        </p>

        <p class="ldr-booking-detail__purpose">{{ $booking->purpose }}</p>
    </header>

    @if ($booking->status === App\Enums\BookingStatus::Rejected && $booking->rejection_reason)
        <div class="ldr-alert ldr-alert--danger" role="alert">
            <strong>{{ __('ui.booking.reason') }}:</strong>
            {{ $booking->rejection_reason }}
        </div>
    @endif

    <section class="ldr-card ldr-booking-detail__schedule">
        <h2>{{ __('ui.booking.schedule') }}</h2>

        <dl class="ldr-spec-list">
            <div>
                <dt>{{ __('ui.booking.start') }}</dt>
                <dd>{{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.booking.end') }}</dt>
                <dd>{{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}</dd>
            </div>
            @if ($booking->due_at)
                <div>
                    <dt>{{ __('ui.booking.due_at') }}</dt>
                    <dd>{{ $booking->due_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}</dd>
                </div>
            @endif
            <div>
                <dt>{{ __('ui.booking.requested_at') }}</dt>
                <dd>{{ $booking->created_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}</dd>
            </div>
        </dl>
    </section>

    <section class="ldr-card ldr-booking-detail__timeline">
        <h2>{{ __('ui.booking.timeline') }}</h2>

        <x-timeline :timeline="$timeline" :status="$booking->status->value" />
    </section>

    <section class="ldr-card ldr-booking-detail__assets">
        <h2>{{ __('ui.booking.assets') }}</h2>

        @if ($booking->items->isEmpty())
            <p class="ldr-muted">{{ __('ui.booking.no_items') }}</p>
        @else
            <ul class="ldr-booking-items">
                @foreach ($booking->items as $item)
                    @php $photo = $item->asset->photos->first(); @endphp
                    <li class="ldr-booking-items__row">
                        <span class="ldr-thumb">
                            @if ($photo)
                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                     alt="{{ $item->asset->name }}"
                                     loading="lazy"
                                     width="48"
                                     height="48">
                            @else
                                <x-icon name="camera" :size="20" />
                            @endif
                        </span>

                        <span class="ldr-booking-items__main">
                            <span class="ldr-booking-items__name">{{ $item->asset->name }}</span>
                            <span class="ldr-mono ldr-muted">{{ $item->asset->code }}</span>
                        </span>

                        <x-status-badge type="item" :status="$item->status->value" />
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @can('cancel', $booking)
        <div class="ldr-detail-actions">
            <button type="button"
                    class="ldr-btn ldr-btn--danger ldr-btn--block"
                    data-confirm-open="cancel-booking-{{ $booking->id }}">
                {{ __('ui.booking.cancel_pending') }}
            </button>
        </div>

        <x-confirm-modal
            :id="'cancel-booking-'.$booking->id"
            :title="__('ui.booking.cancel_title')"
            :message="__('ui.booking.cancel_message', ['code' => $booking->code])"
            :action="route('borrower.bookings.cancel', $booking)"
            :confirm-label="__('ui.booking.cancel_confirm')"
            variant="danger"
        />
    @endcan
    </div>
@endsection
