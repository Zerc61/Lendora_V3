@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@section('content')
    <h1>Buat tugas perawatan</h1>

    <form method="POST" action="{{ route('admin.maintenances.store') }}">
        @csrf

        <label for="asset_id">Aset</label>
        <select id="asset_id" name="asset_id" required>
            @foreach ($assets as $asset)
                <option value="{{ $asset->id }}">{{ $asset->code }} · {{ $asset->name }}</option>
            @endforeach
        </select>

        <label for="assigned_to">Teknisi</label>
        <select id="assigned_to" name="assigned_to">
            <option value="">Belum ditugaskan</option>
            @foreach ($technicians as $technician)
                <option value="{{ $technician->id }}">{{ $technician->name }}</option>
            @endforeach
        </select>

        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" required></textarea>

        <button type="submit">Simpan</button>
    </form>
@endsection
