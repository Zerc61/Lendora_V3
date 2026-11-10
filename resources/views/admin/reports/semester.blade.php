@extends('layouts.admin')

@section('title', __('ui.semester_report.title'))

@php
    $statusTones = [
        'kritis' => 'danger',
        'perlu_tambahan' => 'warning',
        'seimbang' => 'success',
    ];

    $priorityLabels = [
        'urgent' => __('ui.semester_report.priority_urgent'),
        'peningkatan' => __('ui.semester_report.priority_peningkatan'),
    ];

    $statusLabels = [
        'kritis' => __('ui.semester_report.status_kritis'),
        'perlu_tambahan' => __('ui.semester_report.status_perlu_tambahan'),
        'seimbang' => __('ui.semester_report.status_seimbang'),
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.semester_report.eyebrow') }}</span>
            <h1>{{ __('ui.semester_report.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.semester_report.subtitle') }}</p>
            <span class="ldr-prodi-head__meta">
                <x-icon name="calendar" :size="14" />
                {{ $semester['label'] }} · {{ $semester['term'] }} {{ $semester['year'] }}
            </span>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.reports.semester.export') }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.semester_report.export') }}
            </a>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.semester_report.kpi_hours') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="calendar" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ number_format($kpis['demand_hours'], 0, ',', '.') }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.semester_report.kpi_hours_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.semester_report.kpi_utilization') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['utilization'] }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.semester_report.kpi_utilization_hint', ['hours' => number_format($kpis['capacity_hours'], 0, ',', '.')]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.semester_report.kpi_incidents') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="wrench" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">Rp {{ number_format($kpis['maintenance_cost'], 0, ',', '.') }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.semester_report.kpi_incidents_hint', ['count' => $kpis['incidents']]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.semester_report.kpi_rejection') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['rejection_rate'] }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.semester_report.kpi_rejection_hint', ['count' => $kpis['rejected']]) }}</span>
        </div>
    </div>

    @if ($rows->isEmpty())
        <x-empty-state
            icon="bar-chart"
            :title="__('ui.semester_report.empty_title')"
            :description="__('ui.semester_report.empty_description')"
        />
    @else
        <h2 class="ldr-section-title">{{ __('ui.semester_report.table_title') }}</h2>
        <p class="ldr-muted ldr-section-hint">{{ __('ui.semester_report.table_hint', ['count' => $kpis['bottlenecks']]) }}</p>

        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.semester_report.col_category') }}</th>
                            <th>{{ __('ui.semester_report.col_units') }}</th>
                            <th>{{ __('ui.semester_report.col_demand') }}</th>
                            <th>{{ __('ui.semester_report.col_capacity') }}</th>
                            <th>{{ __('ui.semester_report.col_utilization') }}</th>
                            <th>{{ __('ui.semester_report.col_requests') }}</th>
                            <th>{{ __('ui.semester_report.col_rejected') }}</th>
                            <th>{{ __('ui.semester_report.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $width = min(100, (int) round($row['utilization']));
                            @endphp
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $row['category']->name }}</span>
                                </td>
                                <td>{{ $row['units'] }}</td>
                                <td>{{ __('ui.semester_report.hours', ['hours' => $row['demand_hours']]) }}</td>
                                <td>{{ __('ui.semester_report.hours', ['hours' => $row['capacity_hours']]) }}</td>
                                <td>
                                    <span class="ldr-table__name">{{ $row['utilization'] }}%</span>
                                    <div class="ldr-prodi-bar__track" style="margin-top: 4px;">
                                        <span class="ldr-prodi-bar__fill" style="width: {{ $width }}%"></span>
                                    </div>
                                </td>
                                <td>{{ $row['requests'] }}</td>
                                <td>
                                    {{ $row['rejected'] }}
                                    <span class="ldr-table__sub">{{ $row['rejection_rate'] }}%</span>
                                </td>
                                <td>
                                    <span class="ldr-badge ldr-badge--{{ $statusTones[$row['status']] ?? 'neutral' }}">
                                        {{ $statusLabels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <h2 class="ldr-section-title">{{ __('ui.semester_report.rab_title') }}</h2>
    <p class="ldr-muted ldr-section-hint">{{ __('ui.semester_report.rab_hint') }}</p>

    @if ($procurement->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            :title="__('ui.semester_report.rab_empty_title')"
            :description="__('ui.semester_report.rab_empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.semester_report.col_category') }}</th>
                            <th>{{ __('ui.semester_report.col_reason') }}</th>
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
                                    <span class="ldr-table__sub">{{ __('ui.semester_report.reason_detail', ['rejected' => $item['rejected'], 'rate' => $item['rejection_rate']]) }}</span>
                                </td>
                                <td>{{ __('ui.semester_report.reason_rule', ['units' => $item['units'], 'accepted' => $item['requests'] - $item['rejected']]) }}</td>
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
                                    <span class="ldr-badge ldr-badge--{{ $item['priority'] === 'urgent' ? 'danger' : 'warning' }}">
                                        {{ $priorityLabels[$item['priority']] ?? $item['priority'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">{{ __('ui.semester_report.rab_total') }}</th>
                            <th colspan="2">Rp {{ number_format($rab_total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <section class="ldr-semester-sign" aria-labelledby="semester-sign-title">
        <h2 id="semester-sign-title" class="ldr-section-title">{{ __('ui.semester_report.sign_title') }}</h2>
        <p class="ldr-muted">{{ __('ui.semester_report.sign_hint') }}</p>

        <div class="ldr-semester-sign__grid">
            <div class="ldr-semester-sign__party">
                <span class="ldr-table__sub">{{ __('ui.semester_report.sign_submitter') }}</span>
                <strong>{{ $prodi->name }}</strong>
                <span class="ldr-muted">{{ __('ui.semester_report.sign_role') }}</span>
                <span class="ldr-table__sub">{{ $prodi->organization?->name }}</span>
            </div>

            <div class="ldr-semester-sign__party">
                <span class="ldr-table__sub">{{ __('ui.semester_report.sign_document') }}</span>
                <strong>{{ $semester['document'] }}</strong>
                <span class="ldr-muted">{{ __('ui.semester_report.sign_generated', ['date' => $semester['generated_at']]) }}</span>
            </div>
        </div>
    </section>
@endsection
