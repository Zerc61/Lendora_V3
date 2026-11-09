@extends('layouts.borrower')

@section('title', __('ui.booking.my'))
@section('nav', 'bookings')

@php
    $formatRange = function ($booking): string {
        $start = $booking->requested_start_date;
        $end = $booking->requested_end_date;

        if ($start->isSameDay($end)) {
            return $start->copy()->locale('id')->translatedFormat('d M Y');
        }

        if ($start->format('M Y') === $end->format('M Y')) {
            return $start->copy()->locale('id')->translatedFormat('d').'–'.$end->copy()->locale('id')->translatedFormat('d M Y');
        }

        return $start->copy()->locale('id')->translatedFormat('d M Y').' – '.$end->copy()->locale('id')->translatedFormat('d M Y');
    };

    $statusIndex = [
        'pending' => 1,
        'approved' => 2,
        'active' => 3,
        'returned' => 4,
        'rejected' => 1,
        'cancelled' => 1,
    ];
@endphp

@section('content')
    <div class="ldr-page-title">
        <h1>{{ __('ui.booking.my') }}</h1>
        <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.catalog.index') }}">
            {{ __('ui.booking.new') }}
        </a>
    </div>

    <x-status-tabs
        route="borrower.bookings.index"
        :current="$tab"
        :counts="$counts"
    />

    @if ($bookings->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            :title="$tab ? __('ui.booking.empty_tab') : __('ui.booking.empty')"
            :description="__('ui.booking.empty_description')"
        >
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
                {{ __('ui.nav.catalog') }}
            </a>
        </x-empty-state>
    @else
        <div class="ldr-booking-list">
            @foreach ($bookings as $booking)
                @php
                    $reached = $statusIndex[$booking->status->value] ?? 1;
                    $extraItems = max(0, $booking->items->count() - 2);
                @endphp

                <article class="ldr-booking-card">
                    <a class="ldr-booking-card__link" href="{{ route('borrower.bookings.show', $booking) }}">
                        <div class="ldr-booking-card__head">
                            <span class="ldr-mono ldr-booking-card__code">{{ $booking->code }}</span>
                            <x-status-badge type="booking" :status="$booking->status->value" />
                        </div>

                        <p class="ldr-booking-card__dates">
                            <x-icon name="calendar" :size="16" />
                            {{ $formatRange($booking) }}
                        </p>

                        <ul class="ldr-booking-card__items">
                            @foreach ($booking->items->take(2) as $item)
                                <li>
                                    <span class="ldr-thumb ldr-thumb--sm">
                                        @php $photo = $item->asset->photos->first(); @endphp
                                        @if ($photo)
                                            <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                                 alt="{{ $item->asset->name }}"
                                                 loading="lazy"
                                                 width="40"
                                                 height="40">
                                        @else
                                            <x-icon name="camera" :size="16" />
                                        @endif
                                    </span>
                                    <span>{{ $item->asset->name }}</span>
                                </li>
                            @endforeach

                            @if ($extraItems > 0)
                                <li class="ldr-booking-card__more">+{{ $extraItems }} lainnya</li>
                            @endif
                        </ul>

                        <div class="ldr-booking-card__progress" aria-hidden="true">
                            @for ($dot = 1; $dot <= 5; $dot++)
                                <span class="ldr-booking-card__dot {{ $dot <= $reached ? 'is-done' : '' }}"></span>
                            @endfor
                        </div>
                    </a>

                    <div class="ldr-booking-card__actions">
                        @can('cancel', $booking)
                            <button type="button"
                                    class="ldr-btn ldr-btn--ghost"
                                    data-confirm-open="cancel-booking-{{ $booking->id }}">
                                {{ __('ui.booking.cancel_pending') }}
                            </button>
                        @endcan

                        <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.bookings.show', $booking) }}">
                            {{ __('ui.booking.detail_action') }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <x-pagination :paginator="$bookings" />
    @endif

    @foreach ($bookings as $booking)
        @can('cancel', $booking)
            <x-confirm-modal
                :id="'cancel-booking-'.$booking->id"
                :title="__('ui.booking.cancel_title')"
                :message="__('ui.booking.cancel_message', ['code' => $booking->code])"
                :action="route('borrower.bookings.cancel', $booking)"
                :confirm-label="__('ui.booking.cancel_confirm')"
                variant="danger"
            />
        @endcan
    @endforeach
@endsection
