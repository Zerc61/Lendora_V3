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

    $operatorKpiIcons = [
        'pending' => 'clipboard-list',
        'approved' => 'check-circle',
        'active' => 'layers',
        'due_today' => 'alert-circle',
    ];

    $operatorKpiHints = [
        'pending' => __('ui.dashboard.operator.hint_pending'),
        'approved' => __('ui.dashboard.operator.hint_approved'),
        'active' => __('ui.dashboard.operator.hint_active'),
        'due_today' => __('ui.dashboard.operator.hint_due_today'),
    ];

    $adminKpiIcons = [
        'assets_total' => 'package',
        'available' => 'check-circle',
        'borrowed' => 'layers',
        'maintenance' => 'wrench',
        'pending' => 'clipboard-list',
        'utilization' => 'activity',
    ];

    $adminKpiHints = [
        'assets_total' => __('ui.dashboard.admin.hint_assets_total'),
        'available' => __('ui.dashboard.admin.hint_available'),
        'borrowed' => __('ui.dashboard.admin.hint_borrowed'),
        'maintenance' => __('ui.dashboard.admin.hint_maintenance'),
        'pending' => __('ui.dashboard.admin.hint_pending'),
        'utilization' => __('ui.dashboard.admin.hint_utilization'),
    ];

    $technicianKpiIcons = [
        'scheduled' => 'calendar',
        'in_progress' => 'wrench',
        'done_this_month' => 'check-circle',
    ];

    $technicianKpiHints = [
        'scheduled' => __('ui.dashboard.technician.hint_scheduled'),
        'in_progress' => __('ui.dashboard.technician.hint_in_progress'),
        'done_this_month' => __('ui.dashboard.technician.hint_done_this_month'),
    ];

    $prodiKpiIcons = [
        'pending' => 'clipboard-list',
        'special' => 'shield-check',
        'active' => 'layers',
        'returned_this_month' => 'check-circle',
    ];

    $prodiKpiHints = [
        'pending' => __('ui.dashboard.prodi.hint_pending'),
        'special' => __('ui.dashboard.prodi.hint_special'),
        'active' => __('ui.dashboard.prodi.hint_active'),
        'returned_this_month' => __('ui.dashboard.prodi.hint_returned_this_month'),
    ];

    $kpiIcons = match ($role) {
        'technician' => $technicianKpiIcons,
        'admin_prodi' => $prodiKpiIcons,
        default => $adminKpiIcons,
    };

    $kpiHints = match ($role) {
        'technician' => $technicianKpiHints,
        'admin_prodi' => $prodiKpiHints,
        default => $adminKpiHints,
    };

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

    $assetSummary = function ($booking): string {
        $names = $booking->items
            ->map(fn ($item) => $item->asset?->name)
            ->filter()
            ->values();

        if ($names->isEmpty()) {
            return '—';
        }

        $shown = $names->take(2)->join(', ');

        return $names->count() > 2
            ? $shown.' +'.($names->count() - 2)
            : $shown;
    };
@endphp

@section('content')
    @if ($role === 'operator')
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.dashboard.operator.title') }}</h1>
                <p class="ldr-muted">{{ __('ui.dashboard.operator.subtitle') }}</p>
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
                    <div class="ldr-kpi__head">
                        <span class="ldr-kpi__label">{{ $kpiLabels[$role][$key] ?? $key }}</span>
                        <span class="ldr-kpi__icon">
                            <x-icon :name="$operatorKpiIcons[$key] ?? 'activity'" :size="18" />
                        </span>
                    </div>
                    <span class="ldr-kpi__value">{{ $value }}</span>
                    <span class="ldr-kpi__hint">{{ $operatorKpiHints[$key] ?? '' }}</span>
                </div>
            @endforeach
        </div>

        <div class="ldr-dash-grid">
            <section class="ldr-table-card">
                <div class="ldr-card-head">
                    <div>
                        <h2>{{ __('ui.dashboard.operator.queue_title') }}</h2>
                        <p>{{ __('ui.dashboard.operator.queue_hint') }}</p>
                    </div>
                    <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.index') }}">
                        {{ __('ui.dashboard.operator.view_all') }}
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>

                @if ($operatorQueue->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.dashboard.operator.queue_empty') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.booking.code') }}</th>
                                    <th>{{ __('ui.booking.borrower') }}</th>
                                    <th>{{ __('ui.booking.assets') }}</th>
                                    <th>{{ __('ui.booking.period') }}</th>
                                    <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operatorQueue as $booking)
                                    <tr>
                                        <td class="ldr-table__code">
                                            <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                        </td>
                                        <td>
                                            <span class="ldr-table__name">{{ $booking->borrower?->name ?? '—' }}</span>
                                            <span class="ldr-table__sub">{{ $booking->borrower?->email }}</span>
                                        </td>
                                        <td>
                                            <span class="ldr-table__name">{{ $assetSummary($booking) }}</span>
                                            <span class="ldr-table__sub">{{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}</span>
                                        </td>
                                        <td class="ldr-table__num">
                                            {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M') }}
                                            –
                                            {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                                        </td>
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
                @endif
            </section>

            <section class="ldr-table-card">
                <div class="ldr-card-head">
                    <div>
                        <h2>{{ __('ui.dashboard.operator.due_title') }}</h2>
                        <p>{{ __('ui.dashboard.operator.due_hint') }}</p>
                    </div>
                </div>

                @if ($operatorDueToday->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.dashboard.operator.due_empty') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.booking.code') }}</th>
                                    <th>{{ __('ui.booking.borrower') }}</th>
                                    <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operatorDueToday as $booking)
                                    <tr>
                                        <td class="ldr-table__code">
                                            <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                            <span class="ldr-table__sub">{{ $assetSummary($booking) }}</span>
                                        </td>
                                        <td>
                                            <span class="ldr-table__name">{{ $booking->borrower?->name ?? '—' }}</span>
                                            <span class="ldr-table__sub">
                                                {{ $booking->due_at?->copy()->locale('id')->translatedFormat('H:i') }} WIB
                                            </span>
                                        </td>
                                        <td>
                                            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                               href="{{ route('admin.handovers.index') }}">
                                                {{ __('ui.dashboard.operator.process') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    @else
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
                    <div class="ldr-kpi__head">
                        <span class="ldr-kpi__label">{{ $kpiLabels[$role][$key] ?? $key }}</span>
                        <span class="ldr-kpi__icon">
                            <x-icon :name="$kpiIcons[$key] ?? 'activity'" :size="18" />
                        </span>
                    </div>
                    <span class="ldr-kpi__value">
                        {{ $key === 'utilization' ? number_format((float) $value, 1, ',', '.').'%' : $value }}
                    </span>
                    <span class="ldr-kpi__hint">{{ $kpiHints[$key] ?? '' }}</span>
                </div>
            @endforeach
        </div>

        @if ($role === 'organization_admin' && $recentActivity->isNotEmpty())
            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.dashboard.recent_activity') }}</h2>
                        <p>{{ __('ui.dashboard.recent_activity_hint') }}</p>
                    </div>
                    <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.activity-logs.index') }}">
                        {{ __('ui.dashboard.view_all') }}
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>

                <ul class="ldr-activity-list">
                    @foreach ($recentActivity as $log)
                        <li class="ldr-activity-item">
                            <span class="ldr-activity-item__dot" aria-hidden="true">
                                <x-icon name="activity" :size="14" />
                            </span>
                            <div class="ldr-activity-item__body">
                                <strong>{{ $log->description ?: '—' }}</strong>
                                <span class="ldr-muted">
                                    {{ $log->user?->name ?? '—' }} ·
                                    {{ $log->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($role === 'admin_prodi' && $specialQueue->isNotEmpty())
            <h2 class="ldr-section-title">{{ __('ui.dashboard.special_queue') }}</h2>
            <p class="ldr-muted ldr-section-hint">{{ __('ui.dashboard.prodi.queue_hint') }}</p>

            <div class="ldr-special-list">
                @foreach ($specialQueue as $booking)
                    <article class="ldr-special-card">
                        <div class="ldr-special-card__head">
                            <div>
                                <a class="ldr-mono ldr-special-card__code" href="{{ route('admin.bookings.show', $booking) }}">
                                    {{ $booking->code }}
                                </a>
                                <strong>{{ $booking->borrower?->name ?? '—' }}</strong>
                                <span class="ldr-muted">
                                    {{ $booking->borrower?->nim ? 'NIM '.$booking->borrower->nim : '' }}
                                    @if ($booking->borrower?->study_program)
                                        · {{ $booking->borrower->study_program }}
                                    @endif
                                </span>
                            </div>
                            <span class="ldr-badge ldr-badge--warning">
                                {{ __('ui.booking.duration_days', ['count' => $booking->durationInDays()]) }}
                            </span>
                        </div>

                        <p class="ldr-special-card__purpose">{{ $booking->purpose }}</p>

                        <div class="ldr-special-card__facts">
                            <span>
                                <x-icon name="calendar" :size="15" />
                                {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                                –
                                {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                            </span>
                            <span>
                                <x-icon name="package" :size="15" />
                                {{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}
                            </span>
                        </div>

                        <div class="ldr-special-card__actions">
                            <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                               href="{{ route('admin.bookings.show', $booking) }}">
                                {{ __('ui.dashboard.prodi.review') }}
                                <x-icon name="arrow-right" :size="16" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($role === 'technician' && $tasks->isNotEmpty())
            <h2 class="ldr-section-title">{{ __('ui.dashboard.my_tasks') }}</h2>

            <div class="ldr-workorder-list">
                @foreach ($tasks as $task)
                    @php
                        $photo = $task->asset?->photos->first();
                    @endphp
                    <article class="ldr-workorder">
                        <span class="ldr-workorder__media">
                            @if ($photo)
                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                     alt="{{ $task->asset->name }}"
                                     loading="lazy"
                                     width="64"
                                     height="64">
                            @else
                                <x-icon name="camera" :size="22" />
                            @endif
                        </span>

                        <div class="ldr-workorder__body">
                            <div class="ldr-workorder__head">
                                <span class="ldr-mono">{{ $task->asset?->code }}</span>
                                <x-status-badge type="maintenance" :status="$task->status->value" />
                            </div>
                            <strong>{{ $task->asset?->name ?? '—' }}</strong>
                            <p class="ldr-muted">{{ $task->description }}</p>
                            <span class="ldr-workorder__meta">
                                <x-icon name="user" :size="14" />
                                {{ $task->reportedBy?->name ?? '—' }}
                                @if ($task->started_at)
                                    · {{ $task->started_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                                @endif
                            </span>
                        </div>

                        <div class="ldr-workorder__actions">
                            @if ($task->status === App\Enums\MaintenanceStatus::Scheduled)
                                <form method="POST" action="{{ route('admin.maintenances.start', $task) }}">
                                    @csrf
                                    <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--sm">
                                        {{ __('ui.maintenance.start') }}
                                    </button>
                                </form>
                            @elseif ($task->status === App\Enums\MaintenanceStatus::InProgress)
                                <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                   href="{{ route('admin.maintenances.finish', $task) }}">
                                    {{ __('ui.maintenance.finish') }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endif
@endsection
