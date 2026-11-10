@extends('layouts.admin')

@section('title', __('ui.nav.reports'))

@php
    $from = \Carbon\Carbon::parse($filters['date_from']);
    $to = \Carbon\Carbon::parse($filters['date_to']);
    $periodLabel = $from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y');
    $avgDays = $kpis['total_loans'] > 0 ? round($kpis['borrowed_days'] / $kpis['total_loans'], 1) : 0;

    $exportQuery = array_filter($filters);
    $printQuery = array_filter($filters);

    $trendValues = $monthlyTrend->pluck('total')->values();
    $trendMax = max(1, (int) $trendValues->max());
    $trendPoints = $trendValues->map(function (int $value, int $index) use ($trendValues, $trendMax): string {
        $x = $trendValues->count() > 1 ? $index / ($trendValues->count() - 1) * 300 : 150;
        $y = 88 - ($value / $trendMax * 72);

        return round($x, 1).','.round($y, 1);
    })->implode(' ');
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W12 · {{ __('ui.nav.reports') }}</span>
            <h1>{{ __('ui.report.title') }}</h1>
            <p class="ldr-muted">{{ $periodLabel }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.reports.export', $exportQuery) }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.report.export') }}
            </a>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.reports.print', $printQuery) }}">
                <x-icon name="printer" :size="16" />
                {{ __('ui.report.print') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="ldr-filter-bar">
        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="report-date-from">{{ __('ui.booking.filter_date_from') }}</label>
            <input id="report-date-from"
                   class="ldr-input"
                   type="date"
                   name="date_from"
                   value="{{ $filters['date_from'] }}"
                   aria-label="{{ __('ui.booking.filter_date_from') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="report-date-to">{{ __('ui.booking.filter_date_to') }}</label>
            <input id="report-date-to"
                   class="ldr-input"
                   type="date"
                   name="date_to"
                   value="{{ $filters['date_to'] }}"
                   aria-label="{{ __('ui.booking.filter_date_to') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="report-category">{{ __('ui.report.category') }}</label>
            <select id="report-category" class="ldr-input" name="category_id">
                <option value="">{{ __('ui.report.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.reports.index') }}">{{ __('ui.common.reset') }}</a>
        </div>
    </form>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.total_loans') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="arrow-left-right" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['total_loans'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_total_loans') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.borrowed_days') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="calendar" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['borrowed_days'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.avg_days', ['days' => number_format($avgDays, 1, ',', '.')]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.utilization') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="activity" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ number_format((float) $kpis['utilization'], 1, ',', '.') }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_utilization') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.damages') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['damages'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_late_returns', ['count' => $kpis['late_returns']]) }}</span>
        </div>
    </div>

    <div class="ldr-report-grid">
        <section class="ldr-card ldr-report-span-2">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.report.utilization_by_category') }}</h2>
                    <p>{{ __('ui.report.utilization_hint') }}</p>
                </div>
            </div>

            @if ($utilization->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-util-bars">
                    @foreach ($utilization as $row)
                        <div class="ldr-util-row">
                            <div class="ldr-util-row__head">
                                <span>
                                    <strong>{{ $row['name'] }}</strong>
                                    <span class="ldr-muted">{{ __('ui.report.unit_total', ['count' => $row['assets']]) }}</span>
                                </span>
                                <span class="ldr-util-row__value">
                                    {{ number_format((float) $row['utilization'], 1, ',', '.') }}%
                                    <span class="ldr-muted">{{ __('ui.report.borrowed_days_short', ['days' => $row['borrowed_days']]) }}</span>
                                </span>
                            </div>
                            <span class="ldr-util-track">
                                <span class="ldr-util-fill" style="width: {{ min(100, (float) $row['utilization']) }}%"></span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ldr-card">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.report.trend_title') }}</h2>
                    <p>{{ __('ui.report.monthly_trend') }}</p>
                </div>
            </div>

            @if ($monthlyTrend->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-trend">
                    <svg class="ldr-trend__chart" viewBox="0 0 300 96" role="img"
                         aria-label="{{ __('ui.report.trend_title') }}">
                        <polyline class="ldr-trend__line" points="{{ $trendPoints }}" />
                        @if ($trendValues->count() === 1)
                            <circle class="ldr-trend__dot" cx="150" cy="{{ 88 - ($trendValues->first() / $trendMax * 72) }}" r="4" />
                        @endif
                    </svg>

                    <div class="ldr-trend__labels">
                        @foreach ($monthlyTrend as $row)
                            <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['month'])->translatedFormat('M') }}</span>
                        @endforeach
                    </div>

                    <p class="ldr-muted">{{ __('ui.report.trend_peak', ['count' => $monthlyTrend->max('total')]) }}</p>
                </div>
            @endif
        </section>

        <section class="ldr-card ldr-report-span-2">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.category_table_title') }}</h2>

            @if ($utilization->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.report.category') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.assets') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.borrowed_days') }}</th>
                                <th>{{ __('ui.report.utilization') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($utilization as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="ldr-table__num">{{ $row['assets'] }}</td>
                                    <td class="ldr-table__num">{{ $row['borrowed_days'] }}</td>
                                    <td>
                                        <div class="ldr-util-inline">
                                            <span class="ldr-util-track">
                                                <span class="ldr-util-fill" style="width: {{ min(100, (float) $row['utilization']) }}%"></span>
                                            </span>
                                            <strong>{{ number_format((float) $row['utilization'], 1, ',', '.') }}%</strong>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="ldr-card">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.top_borrowers') }}</h2>

            @if ($topBorrowers->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-borrower-list">
                    @foreach ($topBorrowers as $borrower)
                        <div class="ldr-borrower-row">
                            <span class="ldr-apv-avatar" aria-hidden="true">
                                {{ mb_substr($borrower['name'] ?? '?', 0, 2) }}
                            </span>
                            <div>
                                <strong>{{ $borrower['name'] }}</strong>
                                <span class="ldr-muted">
                                    {{ __('ui.report.loans_count', ['count' => $borrower['total']]) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ldr-card ldr-report-span-2">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.report.recent_damages') }}</h2>
                    <p>{{ __('ui.report.damages_hint') }}</p>
                </div>
            </div>

            @if ($damages->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_damages') }}</p>
            @else
                <div class="ldr-damage-list">
                    @foreach ($damages as $damage)
                        <div class="ldr-damage">
                            <span class="ldr-damage__icon">
                                <x-icon name="alert-circle" :size="18" />
                            </span>
                            <div>
                                <strong>{{ $damage->asset?->name }}</strong>
                                <span class="ldr-mono">{{ $damage->asset?->code }}</span>
                                <p>{{ $damage->notes ?: __('ui.report.no_notes') }}</p>
                            </div>
                            <div class="ldr-damage__meta">
                                <span class="ldr-badge ldr-badge--warning">
                                    {{ __('ui.inspection_result.'.$damage->result->value) }}
                                </span>
                                <span class="ldr-muted">
                                    {{ $damage->checked_at?->copy()->locale('id')->translatedFormat('d M Y') ?? '—' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ldr-card">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.top_assets') }}</h2>

            @if ($topAssets->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.nav.assets') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.times_borrowed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topAssets as $asset)
                                <tr>
                                    <td>
                                        <span class="ldr-table__name">{{ $asset['name'] }}</span>
                                        <span class="ldr-table__sub ldr-table__code">{{ $asset['code'] }}</span>
                                    </td>
                                    <td class="ldr-table__num">{{ $asset['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
