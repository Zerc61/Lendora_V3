@extends('layouts.admin')

@section('title', __('ui.student.title'))

@php
    $tierTones = [
        'A+' => 'success',
        'A' => 'success',
        'B+' => 'info',
        'B' => 'info',
        'C' => 'warning',
        'D' => 'danger',
    ];

    $tierOptions = [
        'a_plus' => __('ui.student.tier_a_plus'),
        'a' => __('ui.student.tier_a'),
        'b_plus' => __('ui.student.tier_b_plus'),
        'b' => __('ui.student.tier_b'),
        'c' => __('ui.student.tier_c'),
        'd' => __('ui.student.tier_d'),
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.student.eyebrow') }}</span>
            <h1>{{ __('ui.student.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.student.subtitle') }}</p>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.student.kpi_avg') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">
                {{ $summary['avg_score'] !== null ? number_format((float) $summary['avg_score'], 1, ',', '.') : '—' }}
            </span>
            <span class="ldr-kpi__hint">{{ __('ui.student.kpi_avg_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.student.kpi_teladan') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['teladan'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.student.kpi_teladan_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.student.kpi_pembinaan') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['pembinaan'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.student.kpi_pembinaan_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.student.kpi_penalty') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-list" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['penalty_count'] }}</span>
            <span class="ldr-kpi__hint">
                Rp {{ number_format($summary['penalty_total'], 0, ',', '.') }} ·
                {{ __('ui.student.kpi_penalty_hint', ['unpaid' => $summary['penalty_unpaid']]) }}
            </span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.students.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="student-search">{{ __('ui.student.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="student-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.student.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="student-tier">{{ __('ui.student.filter_tier') }}</label>
            <select id="student-tier" class="ldr-input" name="tier">
                <option value="">{{ __('ui.student.tier_all') }}</option>
                @foreach ($tierOptions as $tier => $label)
                    <option value="{{ $tier }}" @selected(($filters['tier'] ?? null) === $tier)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="student-status">{{ __('ui.student.filter_status') }}</label>
            <select id="student-status" class="ldr-input" name="status">
                <option value="">{{ __('ui.student.status_all') }}</option>
                <option value="active" @selected(($filters['status'] ?? null) === 'active')>{{ __('ui.user.active') }}</option>
                <option value="suspended" @selected(($filters['status'] ?? null) === 'suspended')>{{ __('ui.user.suspended') }}</option>
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="student-sort">{{ __('ui.student.filter_sort') }}</label>
            <select id="student-sort" class="ldr-input" name="sort">
                <option value="lowest" @selected(($filters['sort'] ?? 'lowest') === 'lowest')>{{ __('ui.student.sort_lowest') }}</option>
                <option value="highest" @selected(($filters['sort'] ?? 'lowest') === 'highest')>{{ __('ui.student.sort_highest') }}</option>
                <option value="name" @selected(($filters['sort'] ?? 'lowest') === 'name')>{{ __('ui.student.sort_name') }}</option>
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.students.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($students->isEmpty())
        <x-empty-state
            icon="users"
            :title="__('ui.student.empty_title')"
            :description="__('ui.student.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.student.col_student') }}</th>
                            <th>{{ __('ui.student.col_score') }}</th>
                            <th>{{ __('ui.student.col_usage') }}</th>
                            <th>{{ __('ui.student.col_history') }}</th>
                            <th>{{ __('ui.student.col_status') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.student.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>
                                    <div class="ldr-user-cell">
                                        <span class="ldr-avatar" aria-hidden="true">{{ mb_substr($student->name, 0, 1) }}</span>
                                        <span>
                                            <span class="ldr-table__name">{{ $student->name }}</span>
                                            <span class="ldr-table__sub">
                                                @if ($student->nim) NIM {{ $student->nim }} @endif
                                                @if ($student->study_program) · {{ $student->study_program }} @endif
                                                @if ($student->cohort_year) · {{ $student->cohort_year }} @endif
                                            </span>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if ($student->compliance_score === null)
                                        <span class="ldr-muted">{{ __('ui.student.no_score') }}</span>
                                    @else
                                        <strong>{{ $student->compliance_score }}</strong>
                                        <span class="ldr-badge ldr-badge--{{ $tierTones[$student->tier] ?? 'info' }}">
                                            Tier {{ $student->tier }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ __('ui.student.sessions', ['count' => $student->usage_sessions]) }}</span>
                                    <span class="ldr-table__sub">{{ __('ui.student.days', ['days' => $student->usage_days]) }}</span>
                                </td>
                                <td>
                                    @if ($student->bap_count > 0)
                                        <span class="ldr-table__name">{{ __('ui.student.bap', ['count' => $student->bap_count]) }}</span>
                                        <span class="ldr-table__sub">
                                            Rp {{ number_format((int) $student->penalty_total, 0, ',', '.') }}
                                            @if ($student->unpaid_count > 0)
                                                · {{ __('ui.student.unpaid', ['count' => $student->unpaid_count]) }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="ldr-muted">{{ __('ui.student.clean') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-badge {{ $student->status === 'suspended' ? 'ldr-badge--danger' : 'ldr-badge--success' }}">
                                        {{ $student->status === 'suspended' ? __('ui.user.suspended') : __('ui.user.active') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
                                        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                           href="{{ route('admin.bookings.index', ['status' => 'all', 'search' => $student->name]) }}">
                                            {{ __('ui.student.detail') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$students" />
        </div>
    @endif
@endsection
