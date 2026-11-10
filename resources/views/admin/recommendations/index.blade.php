@extends('layouts.admin')

@section('title', __('ui.recommendation.title'))

@php
    $priorityTones = [
        'urgent' => 'danger',
        'peningkatan' => 'warning',
    ];

    $priorityLabels = [
        'urgent' => __('ui.semester_report.priority_urgent'),
        'peningkatan' => __('ui.semester_report.priority_peningkatan'),
    ];

    $totalUnits = (int) $procurement->sum('extra_units');
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.recommendation.eyebrow') }}</span>
            <h1>{{ __('ui.recommendation.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.recommendation.subtitle') }}</p>
            <span class="ldr-prodi-head__meta">
                <x-icon name="calendar" :size="14" />
                {{ $semester['label'] }} · {{ $semester['term'] }} {{ $semester['year'] }}
            </span>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.reports.semester') }}">
                <x-icon name="clipboard-list" :size="16" />
                {{ __('ui.recommendation.to_report') }}
            </a>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.recommendation.kpi_bottleneck') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['bottlenecks'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.recommendation.kpi_bottleneck_hint') }}</span>
        </div>

        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.recommendation.kpi_rejected') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="x" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['rejected'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.recommendation.kpi_rejected_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.recommendation.kpi_units') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="package" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $totalUnits }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.recommendation.kpi_units_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.recommendation.kpi_budget') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">Rp {{ number_format($rab_total, 0, ',', '.') }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.recommendation.kpi_budget_hint') }}</span>
        </div>
    </div>

    <h2 class="ldr-section-title">{{ __('ui.recommendation.table_title') }}</h2>
    <p class="ldr-muted ldr-section-hint">{{ __('ui.recommendation.table_hint') }}</p>

    @if ($procurement->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            :title="__('ui.recommendation.empty_title')"
            :description="__('ui.recommendation.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.semester_report.col_category') }}</th>
                            <th>{{ __('ui.recommendation.col_demand') }}</th>
                            <th>{{ __('ui.recommendation.col_rejection') }}</th>
                            <th>{{ __('ui.semester_report.col_extra_units') }}</th>
                            <th>{{ __('ui.semester_report.col_unit_price') }}</th>
                            <th>{{ __('ui.semester_report.col_subtotal') }}</th>
                            <th>{{ __('ui.semester_report.col_priority') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($procurement as $item)
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $item['category']->name }}</span>
                                    <span class="ldr-table__sub">{{ __('ui.recommendation.units_now', ['count' => $item['units']]) }}</span>
                                </td>
                                <td>{{ __('ui.recommendation.demand_value', ['accepted' => $item['requests'] - $item['rejected'], 'total' => $item['requests']]) }}</td>
                                <td>
                                    {{ __('ui.recommendation.rejection_value', ['rejected' => $item['rejected'], 'rate' => $item['rejection_rate']]) }}
                                </td>
                                <td>{{ $item['extra_units'] }}</td>
                                <td>
                                    @if ($item['unit_price'] !== null)
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($item['unit_price'] !== null)
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-badge ldr-badge--{{ $priorityTones[$item['priority']] ?? 'neutral' }}">
                                        {{ $priorityLabels[$item['priority']] ?? $item['priority'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">{{ __('ui.semester_report.rab_total') }}</th>
                            <th colspan="2">Rp {{ number_format($rab_total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <p class="ldr-muted ldr-section-hint">{{ __('ui.recommendation.disclaimer') }}</p>
@endsection
