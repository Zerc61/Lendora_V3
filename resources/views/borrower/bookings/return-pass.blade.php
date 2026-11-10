@extends(($pdf ?? false) ? 'layouts.pdf' : 'layouts.borrower')

@section('title', $booking->code)
@section('nav', 'bookings')

@section('content')
    <div class="ldr-return-pass">
        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('borrower.bookings.return-pass', $booking) }}?format=pdf">{{ __('ui.common.download_pdf') }}</a>
        <header class="ldr-return-pass__head">
            <h1>{{ __('ui.booking.qr_pass') }}</h1>
            <p class="ldr-muted">{{ $booking->code }}</p>
        </header>

        <div class="ldr-qr-pass">
            <div class="ldr-qr-code" id="qr-code">
                <svg id="qr-svg" data-qr-token="{{ $booking->return_token }}" data-size="200"></svg>
            </div>
            <p class="ldr-qr-token">{{ $booking->return_token ?? '—' }}</p>
        </div>

        <div class="ldr-return-pass__info">
            <p><strong>{{ __('ui.booking.asset') }}:</strong> {{ $booking->items->first()->asset->name ?? '—' }}</p>
            <p><strong>{{ __('ui.booking.due_at') }}:</strong> {{ $booking->due_at->format('d M Y H:i') }}</p>
            <p><strong>{{ __('ui.booking.pickup_location') }}:</strong> {{ $booking->pickup_location->name ?? '—' }}</p>
        </div>

        <button class="ldr-btn ldr-btn--primary" onclick="window.print()">
            <x-icon name="printer" :size="16" />
            {{ __('ui.common.print') }}
        </button>
    </div>
@endsection