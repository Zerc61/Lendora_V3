@extends('layouts.print')

@section('title', __('ui.report.print_title'))

@section('content')
    <header class="ldr-page-head">
        <div>
            <h1>{{ __('ui.report.print_title') }}</h1>
            <p class="ldr-muted">
                {{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') }}
                – {{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') }}
            </p>
        </div>
        <button type="button" class="ldr-btn ldr-btn--primary" onclick="window.print()">{{ __('ui.report.print') }}</button>
    </header>

    <div class="ldr-grid ldr-grid--4">
        <div class="ldr-kpi"><span class="ldr-kpi__label">{{ __('ui.report.total_loans') }}</span><span class="ldr-kpi__value">{{ $kpis['total_loans'] }}</span></div>
        <div class="ldr-kpi"><span class="ldr-kpi__label">{{ __('ui.report.borrowed_days') }}</span><span class="ldr-kpi__value">{{ $kpis['borrowed_days'] }}</span></div>
        <div class="ldr-kpi"><span class="ldr-kpi__label">{{ __('ui.report.utilization') }}</span><span class="ldr-kpi__value">{{ $kpis['utilization'] }}%</span></div>
        <div class="ldr-kpi"><span class="ldr-kpi__label">{{ __('ui.report.damages') }}</span><span class="ldr-kpi__value">{{ $kpis['damages'] }}</span></div>
    </div>

    <section class="ldr-card">
        <h2>{{ __('ui.report.utilization_title') }}</h2>
        <table class="ldr-table">
            <thead>
                <tr><th>{{ __('ui.report.category') }}</th><th>{{ __('ui.report.assets') }}</th><th>{{ __('ui.report.borrowed_days') }}</th><th>{{ __('ui.report.utilization') }}</th></tr>
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
    </section>

    <section class="ldr-card">
        <h2>{{ __('ui.report.damages_title') }}</h2>
        <table class="ldr-table">
            <thead>
                <tr><th>{{ __('ui.report.date') }}</th><th>{{ __('ui.report.asset') }}</th><th>{{ __('ui.report.result') }}</th><th>{{ __('ui.report.notes') }}</th></tr>
            </thead>
            <tbody>
                @forelse ($damages as $damage)
                    <tr>
                        <td>{{ $damage->checked_at?->translatedFormat('d M Y') }}</td>
                        <td>{{ $damage->asset?->code }} · {{ $damage->asset?->name }}</td>
                        <td>{{ $damage->result?->value }}</td>
                        <td>{{ $damage->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">{{ __('ui.report.no_damages') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
