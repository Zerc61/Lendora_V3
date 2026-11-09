@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@section('content')
    <h1>Ubah tugas perawatan · {{ $maintenance->asset?->code }}</h1>

    <form method="POST" action="{{ route('admin.maintenances.update', $maintenance) }}">
        @csrf
        @method('PUT')

        <label for="assigned_to">Teknisi</label>
        <select id="assigned_to" name="assigned_to">
            <option value="">Belum ditugaskan</option>
            @foreach ($technicians as $technician)
                <option value="{{ $technician->id }}" @selected($maintenance->assigned_to === $technician->id)>{{ $technician->name }}</option>
            @endforeach
        </select>

        <label for="description">Deskripsi</label>
        <textarea id="description" name="description">{{ $maintenance->description }}</textarea>

        <button type="submit">Simpan</button>
    </form>

    <form method="POST" action="{{ route('admin.maintenances.destroy', $maintenance) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Batalkan tugas</button>
    </form>
@endsection
