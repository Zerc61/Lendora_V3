@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@section('content')
    <h1>Selesaikan perawatan · {{ $maintenance->asset?->code }}</h1>

    <form method="POST" action="{{ route('admin.maintenances.finish.store', $maintenance) }}">
        @csrf

        <label for="result_notes">Hasil perawatan</label>
        <textarea id="result_notes" name="result_notes">{{ $maintenance->result_notes }}</textarea>

        <label for="cost">Biaya (Rp)</label>
        <input id="cost" type="number" name="cost" min="0" value="{{ $maintenance->cost }}">

        <label for="condition_good">
            <input id="condition_good" type="checkbox" name="condition_good" value="1">
            Kondisi unit sudah baik
        </label>

        <button type="submit">Simpan hasil</button>
    </form>
@endsection
