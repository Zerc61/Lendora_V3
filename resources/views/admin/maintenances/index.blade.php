@extends('layouts.admin')

@section('title', __('ui.nav.maintenances'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.maintenances') }}</h1>
            <p class="ldr-muted">{{ __('ui.maintenance.index_description') }}</p>
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

    <form method="GET" action="{{ route('admin.maintenances.index') }}" class="ldr-filter-bar" role="search">
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
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th>{{ __('ui.maintenance.status') }}</th>
                            <th>{{ __('ui.maintenance.technician') }}</th>
                            <th>{{ __('ui.maintenance.reported_by') }}</th>
                            <th class="ldr-table__num">{{ __('ui.maintenance.cost') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($maintenances as $maintenance)
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $maintenance->asset?->name }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $maintenance->asset?->code }}</span>
                                </td>
                                <td><x-status-badge type="maintenance" :status="$maintenance->status->value" /></td>
                                <td>{{ $maintenance->assignedTo?->name ?? __('ui.maintenance.unassigned') }}</td>
                                <td>{{ $maintenance->reportedBy?->name ?? '—' }}</td>
                                <td class="ldr-table__num">
                                    {{ $maintenance->cost !== null ? 'Rp '.number_format($maintenance->cost, 0, ',', '.') : '—' }}
                                </td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
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
