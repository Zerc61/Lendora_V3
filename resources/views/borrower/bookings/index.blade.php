@extends('layouts.borrower')

@section('title', __('ui.nav.bookings'))
@section('nav', 'bookings')

@section('content')
    <div class="ldr-booking-list-page">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.nav.bookings') }}</h1>
                <p class="ldr-muted">{{ __('ui.booking.my') }}</p>
            </div>

            <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.bookings.create') }}">
                <x-icon name="plus" :size="16" />
                {{ __('ui.booking.new') }}
            </a>
        </div>

        <div class="ldr-status-tabs">
            <a href="{{ route('borrower.bookings.index') }}" class="ldr-status-tabs__tab {{ $tab === null ? 'is-active' : '' }}">
                {{ __('ui.common.all') }} <span class="ldr-status-tabs__count">{{ $counts->sum() }}</span>
            </a>
            @foreach (['pending', 'approved', 'active', 'returned', 'rejected', 'cancelled'] as $status)
                <a href="{{ route('borrower.bookings.index', ['status' => $status]) }}" class="ldr-status-tabs__tab {{ $tab === $status ? 'is-active' : '' }}">
                    {{ __('ui.booking_status.' . $status) }} <span class="ldr-status-tabs__count">{{ $counts->get($status, 0) }}</span>
                </a>
            @endforeach
        </div>

        @if ($bookings->isEmpty())
            <x-empty-state
                icon="clipboard-list"
                :title="__('ui.booking.empty')"
                :description="__('ui.booking.empty_description')"
            >
                <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
                    {{ __('ui.nav.catalog') }}
                </a>
            </x-empty-state>
        @else
            <div class="ldr-booking-list">
                @foreach ($bookings as $booking)
                    <article class="ldr-booking-card">
                        <a href="{{ route('borrower.bookings.show', $booking) }}" class="ldr-booking-card__link">
                            <div class="ldr-booking-card__head">
                                <span class="ldr-booking-card__code">{{ $booking->code }}</span>
                                <x-status-badge type="booking" :status="$booking->status->value" class="ldr-booking-card__badge" />
                            </div>

                            <div class="ldr-booking-card__body">
                                <div class="ldr-booking-card__dates">
                                    <x-icon name="calendar" :size="14" />
                                    {{ $booking->requested_start_date->format('d M') }} — {{ $booking->requested_end_date->format('d M Y') }}
                                </div>

                                <ul class="ldr-booking-card__items">
                                    @foreach ($booking->items as $item)
                                        <li>
                                            <x-icon name="package" :size="14" />
                                            {{ $item->asset->name }}
                                        </li>
                                    @endforeach
                                    @if ($booking->items->count() > 3)
                                        <li class="ldr-booking-card__more">+{{ $booking->items->count() - 3 }} lagi</li>
                                    @endif
                                </ul>

                                <div class="ldr-booking-card__progress">
                                    @php
                                        $statusStep = ['pending' => 1, 'approved' => 2, 'active' => 3, 'returned' => 4][$booking->status->value] ?? 0;
                                    @endphp
                                    @foreach (['pending', 'approved', 'active', 'returned'] as $index => $step)
                                        <span class="ldr-booking-card__dot {{ $index + 1 <= $statusStep ? 'is-done' : '' }}"></span>
                                    @endforeach
                                </div>
                            </div>
                        </a>

                        <div class="ldr-booking-card__actions">
                            @if ($booking->status === \App\Enums\BookingStatus::Pending)
                                <form method="POST" action="{{ route('borrower.bookings.cancel', $booking) }}" class="ldr-form-inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="ldr-btn ldr-btn--sm ldr-btn--danger">{{ __('ui.booking.cancel_pending') }}</button>
                                </form>
                            @elseif ($booking->status === \App\Enums\BookingStatus::Active)
                                <a href="{{ route('borrower.bookings.extension.form', $booking) }}" class="ldr-btn ldr-btn--sm ldr-btn--primary">
                                    {{ __('ui.booking.extension') }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$bookings" />
        @endif
    </div>
@endsection