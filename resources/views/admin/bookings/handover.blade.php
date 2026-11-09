@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

@section('content')
    <h1>{{ __('ui.nav.handovers') }} · {{ $booking->code }}</h1>

    <p>{{ $booking->borrower?->name }} · {{ $type->label() }}</p>

    <form method="POST" action="{{ route('admin.bookings.handover.store', $booking) }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type->value }}">

        @foreach ($items as $item)
            <fieldset>
                <legend>{{ $item->asset?->code }} · {{ $item->asset?->name }}</legend>
                <label for="condition-{{ $item->id }}">Kondisi</label>
                <select id="condition-{{ $item->id }}" name="conditions[{{ $item->id }}]" required>
                    @foreach (\App\Enums\AssetCondition::cases() as $condition)
                        <option value="{{ $condition->value }}">{{ $condition->value }}</option>
                    @endforeach
                </select>
            </fieldset>
        @endforeach

        <label for="notes">Catatan</label>
        <textarea id="notes" name="notes" maxlength="255"></textarea>

        <button type="submit">Simpan</button>
    </form>
@endsection
