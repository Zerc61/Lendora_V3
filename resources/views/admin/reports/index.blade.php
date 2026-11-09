@extends('layouts.admin')

@section('title', __('ui.nav.reports'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.reports') }}</h1>
            <p class="ldr-muted">{{ __('ui.report.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary"
               href="{{ route('admin.reports.export', array_filter($filters)) }}">
                <x-icon name="bar-chart" :size="18" />
                {{ __('ui.report.export') }}
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
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.reports.index') }}">
                {{ __('ui.common.reset') }}
            </a>
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
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.borrowed_days') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="calendar" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['borrowed_days'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_borrowed_days') }}</span>
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
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_damages') }}</span>
        </div>
        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.report.late_returns') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['late_returns'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.report.hint_late_returns') }}</span>
        </div>
    </div>

    <h2 class="ldr-section-title">{{ __('ui.report.utilization_by_category') }}</h2>

    @if ($utilization->isEmpty())
        <x-empty-state
            icon="bar-chart"
            :title="__('ui.report.empty_title')"
            :description="__('ui.report.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.report.category') }}</th>
                            <th class="ldr-table__num">{{ __('ui.report.assets') }}</th>
                            <th class="ldr-table__num">{{ __('ui.report.borrowed_days') }}</th>
                            <th class="ldr-table__num">{{ __('ui.report.utilization') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($utilization as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td class="ldr-table__num">{{ $row['assets'] }}</td>
                                <td class="ldr-table__num">{{ $row['borrowed_days'] }}</td>
                                <td class="ldr-table__num">{{ number_format((float) $row['utilization'], 1, ',', '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="ldr-form-grid ldr-form-grid--spaced">
        <section class="ldr-card">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.monthly_trend') }}</h2>

            @if ($monthlyTrend->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.report.month') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.total_loans') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.borrowed_days') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyTrend as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row['month'])->locale('id')->translatedFormat('M Y') }}</td>
                                    <td class="ldr-table__num">{{ $row['total'] }}</td>
                                    <td class="ldr-table__num">{{ $row['days'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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

        <section class="ldr-card">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.top_borrowers') }}</h2>

            @if ($topBorrowers->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.booking.borrower') }}</th>
                                <th class="ldr-table__num">{{ __('ui.report.total_loans') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topBorrowers as $borrower)
                                <tr>
                                    <td>{{ $borrower['name'] }}</td>
                                    <td class="ldr-table__num">{{ $borrower['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="ldr-card">
            <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.report.recent_damages') }}</h2>

            @if ($damages->isEmpty())
                <p class="ldr-muted">{{ __('ui.report.no_data') }}</p>
            @else
                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.nav.assets') }}</th>
                                <th>{{ __('ui.inspection.result') }}</th>
                                <th>{{ __('ui.inspection.checked_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($damages as $damage)
                                <tr>
                                    <td>
                                        <span class="ldr-table__name">{{ $damage->asset?->name }}</span>
                                        <span class="ldr-table__sub ldr-table__code">{{ $damage->asset?->code }}</span>
                                    </td>
                                    <td>{{ __('ui.inspection_result.'.$damage->result->value) }}</td>
                                    <td class="ldr-table__num">
                                        {{ $damage->checked_at?->copy()->locale('id')->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
