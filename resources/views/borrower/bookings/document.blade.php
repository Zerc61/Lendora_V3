@extends(($pdf ?? false) ? 'layouts.pdf' : 'layouts.borrower')

@section('title', $booking->code)
@section('nav', 'bookings')

@section('content')
    <div class="ldr-print-page" id="print-area">
        <header class="ldr-print-header">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('borrower.bookings.document', ['booking' => $booking, 'type' => $type]) }}?format=pdf">{{ __('ui.common.download_pdf') }}</a>
            <h1>{{ $type === 'letter' ? __('ui.booking.document_letter') : __('ui.penalty.bap_title') }}</h1>
            <p class="ldr-print-meta">{{ $booking->code }} · {{ $booking->requested_start_date->format('d M Y') }} — {{ $booking->requested_end_date->format('d M Y') }}</p>
        </header>

        <div class="ldr-print-body">
            <div class="ldr-print-section">
                <h3>{{ __('ui.booking.borrower') }}</h3>
                <p>{{ $booking->borrower->name ?? '—' }}</p>
                <p>{{ $booking->borrower->nim ?? '—' }}</p>
            </div>

            <div class="ldr-print-section">
                <h3>{{ __('ui.booking.assets') }}</h3>
                <ul>
                    @foreach ($booking->items as $item)
                        <li>{{ $item->asset->code }} — {{ $item->asset->name }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="ldr-print-section">
                <h3>{{ __('ui.booking.purpose') }}</h3>
                <p>{{ $booking->purpose }}</p>
            </div>
        </div>

        <footer class="ldr-print-footer">
            <p>{{ __('ui.booking.status') }}: {{ __('ui.booking_status.' . $booking->status->value) }}</p>
        </footer>
    </div>

    <script>
        window.print();
    </script>
@endsection