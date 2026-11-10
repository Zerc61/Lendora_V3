@extends(($pdf ?? false) ? 'layouts.pdf' : 'layouts.print')

@section('title', $type === \App\Enums\HandoverType::Pickup ? 'Bukti Pengambilan' : 'Bukti Pengembalian')

@section('content')
    <header class="ldr-page-head">
        <div>
            <h1>{{ $type === \App\Enums\HandoverType::Pickup ? 'Bukti Pengambilan' : 'Bukti Pengembalian' }}</h1>
            <p class="ldr-muted">{{ $handover->code }} · {{ $booking->code }}</p>
        </div>
        <div class="ldr-print-actions">
            <button type="button" class="ldr-btn ldr-btn--primary" onclick="window.print()">Cetak</button>
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.bookings.receipt', ['booking' => $booking, 'type' => $type->value]) }}?format=pdf">{{ __('ui.common.download_pdf') }}</a>
        </div>
    </header>

    <section class="ldr-card">
        <p><strong>Peminjam:</strong> {{ $booking->borrower->name }}</p>
        <p><strong>Waktu:</strong> {{ $handover->happened_at->format('d M Y H:i') }}</p>
        <p><strong>Petugas:</strong> {{ $handover->performedBy->name }}</p>
        @if ($handover->shift_label)
            <p><strong>Shift:</strong> {{ $handover->shift_label }}</p>
        @endif
    </section>

    <table class="ldr-table">
        <thead>
            <tr>
                <th>Unit</th>
                <th>Kode</th>
                <th>Kondisi</th>
                <th>Kelengkapan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($handover->items as $item)
                <tr>
                    <td>{{ $item->bookingItem->asset->name }}</td>
                    <td>{{ $item->bookingItem->asset->code }}</td>
                    <td>{{ __('ui.condition.' . $item->condition->value) }}</td>
                    <td>{{ implode(', ', $item->accessories ?? []) ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($handover->notes)
        <p><strong>Catatan:</strong> {{ $handover->notes }}</p>
    @endif
@endsection
