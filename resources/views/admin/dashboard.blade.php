@extends('layouts.admin')

@section('title', __('ui.nav.dashboard'))

@php
    $kpiLabels = [
        'operator' => [
            'pending' => __('ui.dashboard.operator.pending'),
            'approved' => __('ui.dashboard.operator.approved'),
            'active' => __('ui.dashboard.operator.active'),
            'due_today' => __('ui.dashboard.operator.due_today'),
        ],
        'organization_admin' => [
            'assets_total' => __('ui.dashboard.admin.assets_total'),
            'available' => __('ui.dashboard.admin.available'),
            'borrowed' => __('ui.dashboard.admin.borrowed'),
            'maintenance' => __('ui.dashboard.admin.maintenance'),
            'pending' => __('ui.dashboard.admin.pending'),
            'utilization' => __('ui.dashboard.admin.utilization'),
        ],
        'admin_prodi' => [
            'pending' => __('ui.dashboard.prodi.pending'),
            'special' => __('ui.dashboard.prodi.special'),
            'active' => __('ui.dashboard.prodi.active'),
            'returned_this_month' => __('ui.dashboard.prodi.returned_this_month'),
        ],
        'technician' => [
            'scheduled' => __('ui.dashboard.technician.scheduled'),
            'in_progress' => __('ui.dashboard.technician.in_progress'),
            'done_this_month' => __('ui.dashboard.technician.done_this_month'),
        ],
    ];

    $quickActions = match ($role) {
        'operator' => [
            ['label' => __('ui.booking.queue'), 'href' => route('admin.bookings.index'), 'icon' => 'clipboard-list'],
            ['label' => __('ui.nav.handovers'), 'href' => route('admin.handovers.index'), 'icon' => 'arrow-left-right'],
            ['label' => __('ui.nav.inspections'), 'href' => route('admin.inspections.index'), 'icon' => 'clipboard-check'],
        ],
        'organization_admin' => [
            ['label' => __('ui.nav.assets'), 'href' => route('admin.assets.index'), 'icon' => 'package'],
            ['label' => __('ui.nav.reports'), 'href' => route('admin.reports.index'), 'icon' => 'bar-chart'],
            ['label' => __('ui.nav.users'), 'href' => route('admin.users.index'), 'icon' => 'users'],
        ],
        'admin_prodi' => [
            ['label' => __('ui.booking.queue'), 'href' => route('admin.bookings.index'), 'icon' => 'clipboard-list'],
            ['label' => __('ui.nav.reports'), 'href' => route('admin.reports.index'), 'icon' => 'bar-chart'],
        ],
        'technician' => [
            ['label' => __('ui.nav.maintenances'), 'href' => route('admin.maintenances.index'), 'icon' => 'wrench'],
            ['label' => __('ui.nav.inspections'), 'href' => route('admin.inspections.index'), 'icon' => 'clipboard-check'],
        ],
        default => [],
    };
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.admin.welcome', ['name' => $user->name, 'role' => $user->role->name]) }}</h1>
            <p class="ldr-muted">{{ __('ui.dashboard.subtitle') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            @foreach ($quickActions as $action)
                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ $action['href'] }}">
                    <x-icon :name="$action['icon']" :size="18" />
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="ldr-kpi-grid">
        @foreach ($kpis as $key => $value)
            <div class="ldr-kpi">
                <span class="ldr-kpi__label">{{ $kpiLabels[$role][$key] ?? $key }}</span>
                <span class="ldr-kpi__value">
                    {{ $key === 'utilization' ? number_format((float) $value, 1, ',', '.').'%' : $value }}
                </span>
            </div>
        @endforeach
    </div>

    @if ($role === 'organization_admin' && $recentActivity->isNotEmpty())
        <h2 class="ldr-section-title">{{ __('ui.dashboard.recent_activity') }}</h2>

        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.activity_log.time') }}</th>
                            <th>{{ __('ui.activity_log.user') }}</th>
                            <th>{{ __('ui.activity_log.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentActivity as $log)
                            <tr>
                                <td class="ldr-table__num">
                                    {{ $log->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td>{{ $log->user?->name ?? '—' }}</td>
                                <td>{{ $log->description ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($role === 'admin_prodi' && $specialQueue->isNotEmpty())
        <h2 class="ldr-section-title">{{ __('ui.dashboard.special_queue') }}</h2>

        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.booking.code') }}</th>
                            <th>{{ __('ui.booking.borrower') }}</th>
                            <th>{{ __('ui.booking.period') }}</th>
                            <th class="ldr-table__num">{{ __('ui.booking.duration') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($specialQueue as $booking)
                            <tr>
                                <td class="ldr-table__code">
                                    <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                </td>
                                <td>{{ $booking->borrower?->name }}</td>
                                <td class="ldr-table__num">
                                    {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                                    –
                                    {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                                </td>
                                <td class="ldr-table__num">{{ __('ui.booking.duration_days', ['count' => $booking->durationInDays()]) }}</td>
                                <td>
                                    <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                       href="{{ route('admin.bookings.show', $booking) }}">
                                        {{ __('ui.booking.detail_action') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($role === 'technician' && $tasks->isNotEmpty())
        <h2 class="ldr-section-title">{{ __('ui.dashboard.my_tasks') }}</h2>

        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th>{{ __('ui.booking.filter_status') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $task->asset?->name }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $task->asset?->code }}</span>
                                </td>
                                <td><x-status-badge type="maintenance" :status="$task->status->value" /></td>
                                <td>
                                    <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                       href="{{ route('admin.maintenances.index') }}">
                                        {{ __('ui.booking.detail_action') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
