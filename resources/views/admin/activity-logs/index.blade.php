@extends('layouts.admin')

@section('title', __('ui.nav.activity_logs'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.activity_logs') }}</h1>
            <p class="ldr-muted">{{ __('ui.activity_log.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary"
               href="{{ route('admin.activity-logs.export', array_filter($filters)) }}">
                <x-icon name="arrow-left-right" :size="18" />
                {{ __('ui.activity_log.export') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="log-search">{{ __('ui.activity_log.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="log-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.activity_log.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="log-action">{{ __('ui.activity_log.action') }}</label>
            <select id="log-action" class="ldr-input" name="action">
                <option value="">{{ __('ui.activity_log.all_actions') }}</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? null) === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="log-date-from">{{ __('ui.booking.filter_date_from') }}</label>
            <input id="log-date-from"
                   class="ldr-input"
                   type="date"
                   name="date_from"
                   value="{{ $filters['date_from'] ?? '' }}"
                   aria-label="{{ __('ui.booking.filter_date_from') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="log-date-to">{{ __('ui.booking.filter_date_to') }}</label>
            <input id="log-date-to"
                   class="ldr-input"
                   type="date"
                   name="date_to"
                   value="{{ $filters['date_to'] ?? '' }}"
                   aria-label="{{ __('ui.booking.filter_date_to') }}">
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.activity-logs.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($logs->isEmpty())
        <x-empty-state
            icon="activity"
            :title="__('ui.activity_log.empty_title')"
            :description="__('ui.activity_log.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.activity_log.time') }}</th>
                            <th>{{ __('ui.activity_log.user') }}</th>
                            <th>{{ __('ui.activity_log.action') }}</th>
                            <th>{{ __('ui.activity_log.description') }}</th>
                            <th>{{ __('ui.activity_log.ip_address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="ldr-table__num">
                                    {{ $log->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td>{{ $log->user?->name ?? '—' }}</td>
                                <td class="ldr-table__code">{{ $log->action }}</td>
                                <td>{{ $log->description ?: '—' }}</td>
                                <td class="ldr-table__code">{{ $log->ip_address ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$logs" />
        </div>
    @endif
@endsection
