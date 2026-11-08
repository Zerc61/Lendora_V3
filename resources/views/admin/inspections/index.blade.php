@extends('layouts.admin')

@section('title', __('ui.nav.inspections'))

@section('content')
    <h1>{{ __('ui.nav.inspections') }}</h1>

    <form method="GET" action="{{ route('admin.inspections.index') }}">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari aset">
        <select name="result">
            <option value="">Semua hasil</option>
            @foreach (\App\Enums\InspectionResult::cases() as $result)
                <option value="{{ $result->value }}" @selected(($filters['result'] ?? null) === $result->value)>{{ $result->value }}</option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>

    <a href="{{ route('admin.inspections.create') }}">Catat inspeksi</a>

    <ul>
        @forelse ($inspections as $inspection)
            <li>
                {{ $inspection->checked_at?->format('d M Y H:i') }} ·
                {{ $inspection->asset?->code }} ·
                {{ $inspection->result->value }} ·
                {{ $inspection->inspector?->name }}
            </li>
        @empty
            <li>Belum ada inspeksi.</li>
        @endforelse
    </ul>

    {{ $inspections->links() }}
@endsection
