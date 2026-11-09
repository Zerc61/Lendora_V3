@extends('layouts.admin')

@section('title', __('ui.nav.reports'))

@section('content')
    <h1>{{ __('ui.nav.reports') }}</h1>

    <form method="GET" action="{{ route('admin.reports.index') }}">
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
        <select name="category_id">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit">Terapkan</button>
    </form>

    <a href="{{ route('admin.reports.export', $filters) }}">Ekspor CSV</a>

    <dl>
        <dt>Total peminjaman</dt>
        <dd>{{ $kpis['total_loans'] }}</dd>
        <dt>Total hari pinjam</dt>
        <dd>{{ $kpis['borrowed_days'] }}</dd>
        <dt>Utilisasi</dt>
        <dd>{{ $kpis['utilization'] }}%</dd>
        <dt>Kerusakan</dt>
        <dd>{{ $kpis['damages'] }}</dd>
    </dl>

    <table>
        <thead>
            <tr><th>Kategori</th><th>Unit</th><th>Hari pinjam</th><th>Utilisasi</th></tr>
        </thead>
        <tbody>
            @foreach ($utilization as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['assets'] }}</td>
                    <td>{{ $row['borrowed_days'] }}</td>
                    <td>{{ $row['utilization'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Peminjam teratas</h2>
    <ul>
        @foreach ($topBorrowers as $borrower)
            <li>{{ $borrower['name'] }} · {{ $borrower['total'] }}</li>
        @endforeach
    </ul>

    <h2>Kerusakan terbaru</h2>
    <ul>
        @foreach ($damages as $damage)
            <li>{{ $damage->asset?->code }} · {{ $damage->result->value }} · {{ $damage->checked_at?->format('d M Y') }}</li>
        @endforeach
    </ul>
@endsection
