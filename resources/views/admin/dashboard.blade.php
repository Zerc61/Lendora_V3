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
            'students' => __('ui.dashboard.prodi.students'),
            'low_compliance' => __('ui.dashboard.prodi.low_compliance'),
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
        'students' => 'users',
        'low_compliance' => 'alert-circle',
    ];

    $prodiKpiHints = [
        'pending' => __('ui.dashboard.prodi.hint_pending'),
        'special' => __('ui.dashboard.prodi.hint_special'),
        'active' => __('ui.dashboard.prodi.hint_active'),
        'returned_this_month' => __('ui.dashboard.prodi.hint_returned_this_month'),
        'students' => __('ui.dashboard.prodi.hint_students'),
        'low_compliance' => __('ui.dashboard.prodi.hint_low_compliance'),
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
        @php
            $op = $operatorDashboard;
            $queueCounts = $op['todayQueue']->countBy('tab');
            $shelfLocations = $op['shelfSummary']->pluck('location')->take(2)->join(', ');
            $kpiTiles = [
                'pending' => ['label' => __('ui.dashboard.operator.kpi_pending'), 'hint' => __('ui.dashboard.operator.hint_new_pending'), 'icon' => 'clipboard-list', 'tone' => 'warning'],
                'pickup_today' => ['label' => __('ui.dashboard.operator.kpi_pickup_today'), 'hint' => __('ui.dashboard.operator.hint_pickup_today'), 'icon' => 'arrow-left-right', 'tone' => 'info'],
                'due_today' => ['label' => __('ui.dashboard.operator.kpi_due_today'), 'hint' => __('ui.dashboard.operator.hint_due_today_new'), 'icon' => 'calendar', 'tone' => 'neutral'],
                'late' => ['label' => __('ui.dashboard.operator.kpi_late'), 'hint' => __('ui.dashboard.operator.hint_late'), 'icon' => 'alert-circle', 'tone' => 'danger'],
            ];
        @endphp

        <div class="ldr-op-head">
            <div class="ldr-op-head__intro">
                <div class="ldr-op-head__title">
                    <h1>{{ __('ui.dashboard.operator.title') }}</h1>
                    <span class="ldr-op-live">
                        <span class="ldr-op-live__dot" aria-hidden="true"></span>
                        {{ __('ui.dashboard.operator.live_badge') }}
                    </span>
                </div>
                <p class="ldr-muted">
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    · {{ __('ui.dashboard.operator.subtitle') }}
                </p>
            </div>

            <div class="ldr-op-shift">
                <span class="ldr-op-shift__dot {{ $op['shift']['active'] ? 'is-active' : 'is-idle' }}" aria-hidden="true"></span>
                <div class="ldr-op-shift__body">
                    <span class="ldr-op-shift__label">{{ __('ui.dashboard.operator.shift_title') }}</span>
                    <strong>{{ $op['shift']['label'] }}</strong>
                    <span class="ldr-op-shift__meta">
                        {{ $op['shift']['start'] }}–{{ $op['shift']['end'] }} WIB{{ $shelfLocations !== '' ? ' · '.$shelfLocations : '' }}
                    </span>
                </div>
                <button type="button" class="ldr-btn ldr-btn--secondary ldr-btn--sm" data-modal-open="shift-swap-modal">
                    {{ __('ui.dashboard.operator.swap_shift') }}
                    <x-icon name="arrow-left-right" :size="16" />
                </button>
            </div>
        </div>

        <div class="ldr-op-actions">
            <a class="ldr-btn ldr-btn--primary ldr-btn--sm" href="{{ route('admin.bookings.walk-in') }}">
                <x-icon name="plus" :size="16" />
                {{ __('ui.dashboard.operator.walk_in') }}
            </a>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.approval') }}">
                <x-icon name="clipboard-list" :size="16" />
                {{ __('ui.nav.approval_queue') }}
            </a>
        </div>

        <div class="ldr-kpi-grid">
            @foreach ($kpiTiles as $key => $tile)
                <div class="ldr-kpi ldr-kpi--{{ $tile['tone'] }}">
                    <div class="ldr-kpi__head">
                        <span class="ldr-kpi__label">{{ $tile['label'] }}</span>
                        <span class="ldr-kpi__icon">
                            <x-icon :name="$tile['icon']" :size="18" />
                        </span>
                    </div>
                    <span class="ldr-kpi__value">{{ $op['kpis'][$key] }}</span>
                    <span class="ldr-kpi__hint">{{ $tile['hint'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="ldr-op-grid">
            <section class="ldr-table-card ldr-op-queue">
                <div class="ldr-card-head">
                    <div>
                        <h2>{{ __('ui.dashboard.operator.queue_live') }}</h2>
                        <p>{{ __('ui.dashboard.operator.queue_hint_live') }}</p>
                    </div>
                    <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.index') }}">
                        {{ __('ui.dashboard.operator.view_all') }}
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>

                <div class="ldr-op-tabs" data-op-tabs data-op-target="#op-queue-rows" role="tablist" aria-label="{{ __('ui.dashboard.operator.queue_live') }}">
                    <button type="button" class="ldr-op-tab is-active" data-op-tab="all" role="tab" aria-selected="true">
                        {{ __('ui.dashboard.operator.queue_all') }} ({{ $op['todayQueue']->count() }})
                    </button>
                    <button type="button" class="ldr-op-tab" data-op-tab="approval" role="tab" aria-selected="false">
                        {{ __('ui.dashboard.operator.tab_approval') }} ({{ (int) ($queueCounts['approval'] ?? 0) }})
                    </button>
                    <button type="button" class="ldr-op-tab" data-op-tab="pickup" role="tab" aria-selected="false">
                        {{ __('ui.dashboard.operator.tab_pickup') }} ({{ (int) ($queueCounts['pickup'] ?? 0) }})
                    </button>
                    <button type="button" class="ldr-op-tab" data-op-tab="return" role="tab" aria-selected="false">
                        {{ __('ui.dashboard.operator.tab_return') }} ({{ (int) ($queueCounts['return'] ?? 0) }})
                    </button>
                </div>

                @if ($op['todayQueue']->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.dashboard.operator.queue_empty') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table ldr-op-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.booking.code') }}</th>
                                    <th>{{ __('ui.booking.borrower') }}</th>
                                    <th>{{ __('ui.booking.assets') }}</th>
                                    <th>{{ __('ui.booking.period') }}</th>
                                    <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="op-queue-rows">
                                @foreach ($op['todayQueue'] as $item)
                                    @php
                                        $booking = $item['booking'];
                                        $blocked = $item['block_reason'] !== null;
                                    @endphp
                                    <tr data-op-row="{{ $item['tab'] }}">
                                        <td class="ldr-table__code">
                                            <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                            <span class="ldr-op-tag ldr-op-tag--{{ $item['tab'] }}">{{ $item['label'] }}</span>
                                        </td>
                                        <td>
                                            <span class="ldr-table__name">{{ $booking->borrower?->name ?? '—' }}</span>
                                            <span class="ldr-table__sub">{{ $booking->borrower?->nim ? 'NIM '.$booking->borrower->nim : $booking->borrower?->email }}</span>
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
                                            <div class="ldr-op-row-actions">
                                                @if ($item['tab'] === 'approval')
                                                    <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                                        @csrf
                                                        <button class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                                                type="submit"
                                                                @disabled($blocked)
                                                                @if ($blocked) title="{{ $item['block_reason'] }}" @endif>
                                                            {{ __('ui.dashboard.operator.approve_action') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                                       href="{{ route('admin.bookings.handover', $booking) }}">
                                                        {{ $item['tab'] === 'pickup' ? __('ui.dashboard.operator.pickup_action') : __('ui.dashboard.operator.return_action') }}
                                                    </a>
                                                @endif
                                                <a class="ldr-btn ldr-btn--ghost ldr-btn--sm"
                                                   href="{{ route('admin.bookings.show', $booking) }}">
                                                    {{ __('ui.dashboard.operator.detail_action') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @if ($blocked)
                                        <tr class="ldr-table__banner-row" data-op-row="{{ $item['tab'] }}">
                                            <td colspan="5">
                                                <p class="ldr-op-block">
                                                    <x-icon name="alert-circle" :size="14" />
                                                    {{ $item['block_reason'] }}
                                                </p>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <aside class="ldr-op-side">
                <section class="ldr-card ldr-op-panel">
                    <div class="ldr-card-head ldr-card-head--flush">
                        <div>
                            <h2>{{ __('ui.dashboard.operator.schedule_title') }}</h2>
                        </div>
                        <span class="ldr-op-panel__hint">{{ now()->locale('id')->translatedFormat('d M') }}</span>
                    </div>

                    @if ($op['handoverSchedule']->isEmpty())
                        <p class="ldr-dash-empty">{{ __('ui.dashboard.operator.schedule_empty') }}</p>
                    @else
                        <ol class="ldr-op-timeline">
                            @foreach ($op['handoverSchedule'] as $entry)
                                <li class="ldr-op-timeline__item">
                                    <span class="ldr-op-timeline__dot" aria-hidden="true"></span>
                                    <div class="ldr-op-timeline__body">
                                        <div class="ldr-op-timeline__head">
                                            <span class="ldr-op-timeline__time">{{ $entry['time'] }} WIB</span>
                                            <span class="ldr-badge ldr-badge--{{ $entry['activity'] === __('ui.dashboard.operator.activity_pickup') ? 'info' : 'warning' }}">
                                                {{ $entry['activity'] }}
                                            </span>
                                        </div>
                                        <a class="ldr-op-timeline__code" href="{{ route('admin.bookings.show', $entry['booking']) }}">
                                            {{ $entry['booking']->code }}
                                        </a>
                                        <span class="ldr-muted">{{ $entry['booking']->borrower?->name }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <section class="ldr-card ldr-op-panel">
                    <div class="ldr-card-head ldr-card-head--flush">
                        <div>
                            <h2>{{ __('ui.dashboard.operator.shelf_title') }}</h2>
                        </div>
                    </div>

                    @if ($op['shelfSummary']->isEmpty())
                        <p class="ldr-dash-empty">—</p>
                    @else
                        <ul class="ldr-op-shelf">
                            @foreach ($op['shelfSummary'] as $shelf)
                                @php
                                    $ratio = $shelf['total'] > 0 ? (int) round($shelf['available'] / $shelf['total'] * 100) : 0;
                                @endphp
                                <li class="ldr-op-shelf__item">
                                    <div class="ldr-op-shelf__head">
                                        <strong>{{ $shelf['location'] }}</strong>
                                        <span class="ldr-op-shelf__ratio">{{ $ratio }}%</span>
                                    </div>
                                    <div class="ldr-op-shelf__bar" role="img" aria-label="{{ $shelf['available'] }} {{ __('ui.dashboard.operator.shelf_available') }}">
                                        <span class="ldr-op-shelf__fill" style="width: {{ $ratio }}%"></span>
                                    </div>
                                    <span class="ldr-muted">
                                        {{ $shelf['available'] }} {{ __('ui.dashboard.operator.shelf_available') }}
                                        · {{ $shelf['borrowed'] }} {{ __('ui.dashboard.operator.shelf_borrowed') }}
                                        @if ($shelf['maintenance'] > 0)
                                            · {{ $shelf['maintenance'] }} {{ __('ui.dashboard.operator.shelf_maintenance') }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="ldr-card ldr-op-panel">
                    <div class="ldr-card-head ldr-card-head--flush">
                        <div>
                            <h2>{{ __('ui.dashboard.operator.shift_note_title') }}</h2>
                        </div>
                    </div>

                    @if ($op['lastShiftNote'] === null)
                        <p class="ldr-dash-empty">{{ __('ui.dashboard.operator.shift_note_empty') }}</p>
                    @else
                        <blockquote class="ldr-op-note">
                            <p>{{ $op['lastShiftNote']->note }}</p>
                            <footer class="ldr-muted">
                                {{ __('ui.dashboard.operator.shift_note_by', ['name' => $op['lastShiftNote']->user?->name ?? '—']) }}
                                · {{ $op['lastShiftNote']->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                            </footer>
                        </blockquote>
                    @endif
                </section>
            </aside>
        </div>

        <x-modal id="shift-swap-modal" :title="__('ui.dashboard.operator.swap_shift_title')">
            <form method="POST" action="{{ route('admin.shifts.notes.store') }}" class="ldr-stack">
                @csrf
                <label class="ldr-field">
                    <span>{{ __('ui.dashboard.operator.swap_shift_note') }}</span>
                    <textarea class="ldr-input" name="note" rows="4" required maxlength="2000">{{ old('note') }}</textarea>
                </label>
                <button class="ldr-btn ldr-btn--primary" type="submit">{{ __('ui.dashboard.operator.swap_shift_submit') }}</button>
            </form>
        </x-modal>
    @else
        <div class="ldr-page-head">
            <div>
                @if ($role === 'admin_prodi')
                    <span class="ldr-eyebrow">{{ __('ui.prodi.eyebrow') }}</span>
                    <h1>{{ __('ui.prodi.title') }}</h1>
                    <p class="ldr-muted">{{ __('ui.prodi.subtitle') }}</p>
                    <span class="ldr-prodi-head__meta">
                        <x-icon name="calendar" :size="14" />
                        {{ $prodiDashboard['semester']['label'] }}
                    </span>
                @else
                    <h1>{{ __('ui.admin.welcome', ['name' => $user->name, 'role' => $user->role->name]) }}</h1>
                    <p class="ldr-muted">{{ __('ui.dashboard.subtitle') }}</p>
                @endif
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

        @if ($role === 'admin_prodi')
            @php
                $prodi = $prodiDashboard;
                $maxCategory = max(1, (int) ($prodi['categories']->max('count') ?? 0));
            @endphp

            @if ($prodi['queue']->isNotEmpty())
                <div class="ldr-alert ldr-alert--warning ldr-prodi-alert" role="status">
                    <x-icon name="shield-check" :size="18" />
                    <div>
                        <strong>{{ __('ui.prodi.queue_title') }}: {{ $prodi['queue']->count() }}</strong>
                        <p>{{ __('ui.prodi.queue_hint') }}</p>
                    </div>
                    <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.approval') }}">
                        {{ __('ui.prodi.review') }}
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>
            @endif

            <div class="ldr-prodi-grid">
                <div class="ldr-stack">
                    <h2 class="ldr-section-title ldr-section-title--flush">{{ __('ui.prodi.queue_title') }}</h2>
                    <p class="ldr-muted ldr-section-hint">{{ __('ui.prodi.queue_hint') }}</p>

                    @if ($prodi['queue']->isEmpty())
                        <x-empty-state
                            icon="shield-check"
                            :title="__('ui.prodi.queue_empty')"
                            :description="__('ui.dashboard.prodi.queue_hint')"
                        />
                    @else
                        <div class="ldr-special-list">
                            @foreach ($prodi['queue'] as $card)
                                @php
                                    $booking = $card['booking'];
                                    $borrower = $booking->borrower;
                                    $assets = $booking->items->map->asset->filter();
                                @endphp

                                <article class="ldr-special-card ldr-prodi-card">
                                    <div class="ldr-special-card__head">
                                        <div>
                                            <a class="ldr-mono ldr-special-card__code" href="{{ route('admin.bookings.show', $booking) }}">
                                                {{ $booking->code }}
                                            </a>
                                            <strong>{{ $borrower?->name ?? '—' }}</strong>
                                            <span class="ldr-muted">
                                                @if ($borrower?->nim) NIM {{ $borrower->nim }} @endif
                                                @if ($borrower?->study_program) · {{ $borrower->study_program }} @endif
                                                @if ($borrower?->semester) · {{ __('ui.prodi.semester') }} {{ $borrower->semester }} @endif
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
                                        <span>
                                            <x-icon name="clipboard-list" :size="15" />
                                            {{ __('ui.prodi.value') }}: Rp {{ number_format($card['value'], 0, ',', '.') }}
                                        </span>
                                    </div>

                                    @if ($assets->isNotEmpty())
                                        <p class="ldr-prodi-card__assets">
                                            {{ $assets->take(2)->pluck('name')->join(', ') }}
                                            @if ($assets->count() > 2)
                                                +{{ $assets->count() - 2 }}
                                            @endif
                                        </p>
                                    @endif

                                    @if ($card['block_reason'])
                                        <p class="ldr-op-block">
                                            <x-icon name="alert-circle" :size="14" />
                                            {{ $card['block_reason'] }}
                                        </p>
                                    @endif

                                    <div class="ldr-special-card__actions">
                                        <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.show', $booking) }}">
                                            {{ __('ui.prodi.review') }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                            @csrf
                                            <button class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                                    type="submit"
                                                    @disabled(! $card['can_decide'])
                                                    @if (! $card['can_decide']) title="{{ $card['block_reason'] }}" @endif>
                                                {{ __('ui.prodi.approve') }}
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="ldr-prodi-rail">
                    <section class="ldr-card ldr-prodi-panel">
                        <div class="ldr-prodi-panel__head">
                            <span class="ldr-prodi-panel__icon"><x-icon name="shield-check" :size="18" /></span>
                            <div>
                                <h2>{{ __('ui.prodi.rule_title') }}</h2>
                                <p class="ldr-muted">{{ __('ui.dashboard.prodi.queue_hint') }}</p>
                            </div>
                        </div>

                        <ul class="ldr-prodi-rules">
                            <li>{{ __('ui.prodi.rule_duration', ['days' => $prodi['rules']['max_days']]) }}</li>
                            <li>{{ __('ui.prodi.rule_value', ['limit' => number_format($prodi['rules']['operator_limit'], 0, ',', '.')]) }}</li>
                            <li>{{ __('ui.prodi.rule_authority') }}</li>
                        </ul>
                    </section>

                    <section class="ldr-card ldr-prodi-panel">
                        <div class="ldr-prodi-panel__head">
                            <span class="ldr-prodi-panel__icon"><x-icon name="layers" :size="18" /></span>
                            <div>
                                <h2>{{ __('ui.prodi.category_title') }}</h2>
                            </div>
                        </div>

                        @if ($prodi['categories']->isEmpty())
                            <p class="ldr-dash-empty">{{ __('ui.prodi.category_empty') }}</p>
                        @else
                            <ul class="ldr-prodi-bars">
                                @foreach ($prodi['categories'] as $category)
                                    @php $width = (int) round($category['count'] / $maxCategory * 100); @endphp
                                    <li class="ldr-prodi-bar">
                                        <div class="ldr-prodi-bar__head">
                                            <strong>{{ $category['name'] }}</strong>
                                            <span>{{ $category['count'] }}</span>
                                        </div>
                                        <div class="ldr-prodi-bar__track">
                                            <span class="ldr-prodi-bar__fill" style="width: {{ $width }}%"></span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </aside>
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
