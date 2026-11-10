@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@php
    $tabs = [
        ['value' => null, 'label' => __('ui.common.all'), 'count' => $summary['scheduled'] + $summary['in_progress'] + $summary['done']],
        ['value' => 'scheduled', 'label' => __('ui.maintenance_status.scheduled'), 'count' => $summary['scheduled']],
        ['value' => 'in_progress', 'label' => __('ui.maintenance_status.in_progress'), 'count' => $summary['in_progress']],
        ['value' => 'done', 'label' => __('ui.maintenance_status.done'), 'count' => $summary['done']],
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W10 · {{ __('ui.nav.maintenances') }}</span>
            <h1>{{ __('ui.nav.maintenances') }}</h1>
            <p class="ldr-muted">{{ $role === 'technician' ? __('ui.maintenance.technician_description') : __('ui.maintenance.index_description') }}</p>
        </div>

        @if (in_array($role, ['operator', 'organization_admin'], true))
            <div class="ldr-page-head__actions">
                <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.maintenances.create') }}">
                    <x-icon name="wrench" :size="18" />
                    {{ __('ui.maintenance.add') }}
                </a>
            </div>
        @endif
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.maintenance_status.in_progress') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="wrench" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['in_progress'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.maintenance.kpi_progress_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.maintenance_status.scheduled') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="calendar" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['scheduled'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.maintenance.kpi_scheduled_hint') }}</span>
        </div>

        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.maintenance.total_cost') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">Rp {{ number_format($summary['cost'], 0, ',', '.') }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.maintenance.kpi_cost_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.maintenance_status.done') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['done'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.maintenance.kpi_done_hint') }}</span>
        </div>
    </div>

    <nav class="ldr-tab-bar" aria-label="{{ __('ui.maintenance.status') }}">
        @foreach ($tabs as $tab)
            <a class="ldr-tab {{ ($filters['status'] ?? null) === $tab['value'] ? 'is-active' : '' }}"
               href="{{ route('admin.maintenances.index', array_filter(['status' => $tab['value']])) }}">
                {{ $tab['label'] }}
                <span class="ldr-tab__count">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.maintenances.index') }}" class="ldr-filter-bar" role="search">
        @if ($filters['status'] ?? null)
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif

        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="maintenance-search">{{ __('ui.maintenance.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="maintenance-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.maintenance.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="maintenance-status">{{ __('ui.booking.filter_status') }}</label>
            <select id="maintenance-status" class="ldr-input" name="status">
                <option value="">{{ __('ui.common.all') }}</option>
                @foreach (\App\Enums\MaintenanceStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ __('ui.maintenance_status.'.$status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.maintenances.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($maintenances->isEmpty())
        <x-empty-state
            icon="wrench"
            :title="__('ui.maintenance.empty_title')"
            :description="__('ui.maintenance.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.maintenance.ticket') }}</th>
                            <th>{{ __('ui.maintenance.complaint') }}</th>
                            <th>{{ __('ui.maintenance.technician') }}</th>
                            <th>{{ __('ui.maintenance.priority') }}</th>
                            <th>{{ __('ui.maintenance.status') }}</th>
                            <th class="ldr-table__num">{{ __('ui.maintenance.cost') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($maintenances as $maintenance)
                            <tr>
                                <td>
                                    <div class="ldr-table__media">
                                        <span class="ldr-thumb ldr-thumb--sm">
                                            @php $photo = $maintenance->asset?->photos->first(); @endphp
                                            @if ($photo)
                                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                                     alt="{{ $maintenance->asset->name }}"
                                                     loading="lazy"
                                                     width="40"
                                                     height="40">
                                            @else
                                                <x-icon name="camera" :size="16" />
                                            @endif
                                        </span>
                                        <span>
                                            <a class="ldr-table__code ldr-mono" href="{{ route('admin.maintenances.show', $maintenance) }}">
                                                {{ $maintenance->code ?? 'MTC-'.$maintenance->id }}
                                            </a>
                                            <span class="ldr-table__name">{{ $maintenance->asset?->name }}</span>
                                            <span class="ldr-table__sub ldr-table__code">{{ $maintenance->asset?->code }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="ldr-table__clamp">{{ \Illuminate\Support\Str::limit($maintenance->description, 72) }}</td>
                                <td>
                                    {{ $maintenance->assignedTo?->name ?? __('ui.maintenance.unassigned') }}
                                    @if ($maintenance->vendor)
                                        <span class="ldr-table__sub">{{ $maintenance->vendor }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-badge ldr-badge--priority-{{ $maintenance->priority?->value ?? 'normal' }}">
                                        {{ __('ui.maintenance_priority.'.($maintenance->priority?->value ?? 'normal')) }}
                                    </span>
                                </td>
                                <td><x-status-badge type="maintenance" :status="$maintenance->status->value" /></td>
                                <td class="ldr-table__num">
                                    {{ $maintenance->cost !== null ? 'Rp '.number_format($maintenance->cost, 0, ',', '.') : '—' }}
                                </td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
                                        <a class="ldr-btn ldr-btn--ghost ldr-btn--sm"
                                           href="{{ route('admin.maintenances.show', $maintenance) }}">
                                            {{ __('ui.inspection.detail') }}
                                        </a>

                                        @if ($role === 'organization_admin')
                                            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                               href="{{ route('admin.maintenances.edit', $maintenance) }}">
                                                {{ __('ui.common.edit') }}
                                            </a>
                                        @endif

                                        @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::Scheduled)
                                            <form method="POST" action="{{ route('admin.maintenances.start', $maintenance) }}">
                                                @csrf
                                                <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--sm">
                                                    {{ __('ui.maintenance.start') }}
                                                </button>
                                            </form>
                                        @endif

                                        @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::InProgress)
                                            <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                               href="{{ route('admin.maintenances.finish', $maintenance) }}">
                                                {{ __('ui.maintenance.finish') }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$maintenances" />
        </div>
    @endif
@endsection
