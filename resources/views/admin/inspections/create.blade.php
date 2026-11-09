@extends('layouts.admin')

@section('title', __('ui.nav.inspections'))

@section('content')
    <h1>Catat inspeksi</h1>

    <form method="POST" action="{{ route('admin.inspections.store') }}">
        @csrf

        <label for="asset_id">Aset</label>
        <select id="asset_id" name="asset_id" required>
            @foreach ($assets as $asset)
                <option value="{{ $asset->id }}" @selected($bookingItem?->asset_id === $asset->id)>{{ $asset->code }} · {{ $asset->name }}</option>
            @endforeach
        </select>

        <label for="booking_item_id">Item peminjaman (opsional)</label>
        <input id="booking_item_id" type="number" name="booking_item_id" value="{{ $bookingItem?->id }}">

        <label for="result">Hasil</label>
        <select id="result" name="result" required>
            @foreach (\App\Enums\InspectionResult::cases() as $result)
                <option value="{{ $result->value }}">{{ $result->value }}</option>
            @endforeach
        </select>

        <label for="notes">Catatan</label>
        <textarea id="notes" name="notes"></textarea>

        <button type="submit">Simpan</button>
    </form>
@endsection
