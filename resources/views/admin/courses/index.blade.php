@extends('layouts.admin')

@section('title', __('ui.course_quota.title'))

@php
    $statusTones = [
        'kritis' => 'danger',
        'normal' => 'info',
        'surplus' => 'success',
        'unset' => 'neutral',
    ];

    $statusLabels = [
        'kritis' => __('ui.course_quota.status_kritis'),
        'normal' => __('ui.course_quota.status_normal'),
        'surplus' => __('ui.course_quota.status_surplus'),
        'unset' => __('ui.course_quota.status_unset'),
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.course_quota.eyebrow') }}</span>
            <h1>{{ __('ui.course_quota.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.course_quota.subtitle') }}</p>
            <span class="ldr-prodi-head__meta">
                <x-icon name="calendar" :size="14" />
                {{ $semester['label'] }}
            </span>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_total') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['total_quota'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_total_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_used') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-list" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['total_used'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_used_hint', ['percent' => $kpis['percent']]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_remaining') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['remaining'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_remaining_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_critical') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['critical'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_critical_hint') }}</span>
        </div>
    </div>

    @if ($courses->isEmpty())
        <x-empty-state
            icon="bar-chart"
            :title="__('ui.course_quota.empty_title')"
            :description="__('ui.course_quota.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.course_quota.col_course') }}</th>
                            <th>{{ __('ui.course_quota.col_lecturer') }}</th>
                            <th>{{ __('ui.course_quota.col_usage') }}</th>
                            <th>{{ __('ui.course_quota.col_remaining') }}</th>
                            <th>{{ __('ui.course_quota.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $row)
                            @php
                                $course = $row['course'];
                                $width = min(100, (int) round($row['percent'] ?? 0));
                            @endphp
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $course->code }} · {{ $course->name }}</span>
                                    <span class="ldr-table__sub">
                                        Semester {{ $course->semester }} · {{ $course->credits }} SKS ·
                                        {{ __('ui.course_quota.sessions', ['count' => $row['sessions']]) }}
                                    </span>
                                </td>
                                <td>{{ $course->lecturer_name ?: '—' }}</td>
                                <td>
                                    @if ($row['quota_hours'] === null)
                                        <span class="ldr-muted">{{ __('ui.course_quota.quota_unset') }}</span>
                                    @else
                                        <span class="ldr-table__name">
                                            {{ __('ui.course_quota.used_of', ['used' => $row['used_hours'], 'quota' => $row['quota_hours']]) }}
                                        </span>
                                        <div class="ldr-prodi-bar__track" style="margin-top: 4px;">
                                            <span class="ldr-prodi-bar__fill" style="width: {{ $width }}%"></span>
                                        </div>
                                        <span class="ldr-table__sub">{{ $row['percent'] }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row['remaining_hours'] !== null)
                                        {{ __('ui.course_quota.hours', ['hours' => $row['remaining_hours']]) }}
                                    @else
                                        —
                                    @endif
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
@endsection
