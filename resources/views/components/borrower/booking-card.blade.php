@props(['booking', 'showActions' => true])

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
        </div>
    </a>

    @if ($showActions)
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
    @endif
</article>