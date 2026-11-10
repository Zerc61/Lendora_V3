@extends('layouts.admin')

@section('title', __('ui.nav.inspections'))

@php
    $fitness = $summary['total'] > 0 ? round(($summary['ok'] / $summary['total']) * 100, 1) : 0;
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W9 · {{ __('ui.nav.inspections') }}</span>
            <h1>{{ __('ui.nav.inspections') }}</h1>
            <p class="ldr-muted">{{ __('ui.inspection.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.inspections.create') }}">
                <x-icon name="clipboard-check" :size="18" />
                {{ __('ui.inspection.add') }}
            </a>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.inspection.kpi_today') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-check" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['today'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.inspection.kpi_today_hint', ['today' => $summary['today']]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.inspection.kpi_repair') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="wrench" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['needs_repair'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.inspection.kpi_repair_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.inspection.kpi_damaged') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['damaged'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.inspection.kpi_damaged_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.inspection.kpi_fitness') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ number_format($fitness, 1, ',', '.') }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.inspection.kpi_fitness_hint') }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.inspections.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="inspection-search">{{ __('ui.inspection.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="inspection-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.inspection.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="inspection-result">{{ __('ui.inspection.result') }}</label>
            <select id="inspection-result" class="ldr-input" name="result">
                <option value="">{{ __('ui.common.all') }}</option>
                @foreach (\App\Enums\InspectionResult::cases() as $result)
                    <option value="{{ $result->value }}" @selected(($filters['result'] ?? null) === $result->value)>
                        {{ __('ui.inspection_result.'.$result->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.inspections.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($inspections->isEmpty())
        <x-empty-state
            icon="clipboard-check"
            :title="__('ui.inspection.empty_title')"
            :description="__('ui.inspection.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-card-head ldr-card-head--flush ldr-table-card__head">
                <div>
                    <h2>{{ __('ui.inspection.history_title') }}</h2>
                    <p>{{ __('ui.inspection.history_hint') }}</p>
                </div>
            </div>

            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.inspection.checked_at') }}</th>
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th>{{ __('ui.inspection.context') }}</th>
                            <th>{{ __('ui.inspection.inspector') }}</th>
                            <th>{{ __('ui.inspection.result') }}</th>
                            <th>{{ __('ui.booking.notes') }}</th>
                            <th><span class="ldr-sr-only">{{ __('ui.common.action') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inspections as $inspection)
                            <tr>
                                <td class="ldr-table__num">
                                    <span class="ldr-table__name">{{ $inspection->checked_at?->copy()->locale('id')->translatedFormat('d M Y') ?? '—' }}</span>
                                    <span class="ldr-table__sub">{{ $inspection->checked_at?->copy()->locale('id')->translatedFormat('H:i') }} WIB</span>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $inspection->asset?->name }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $inspection->asset?->code }}</span>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $inspection->context?->label() ?? '—' }}</span>
                                    @if ($inspection->bookingItem?->booking)
                                        <span class="ldr-table__sub ldr-table__code">{{ $inspection->bookingItem->booking->code }}</span>
                                    @endif
                                </td>
                                <td>{{ $inspection->inspector?->name ?? '—' }}</td>
                                <td>
                                    <span class="ldr-badge {{ $inspection->result === App\Enums\InspectionResult::Ok ? 'ldr-badge--success' : ($inspection->result === App\Enums\InspectionResult::NeedsRepair ? 'ldr-badge--warning' : 'ldr-badge--danger') }}">
                                        {{ __('ui.inspection_result.'.$inspection->result->value) }}
                                    </span>
                                </td>
                                <td class="ldr-table__clamp">{{ $inspection->notes ?: '—' }}</td>
                                <td class="ldr-table__actions">
                                    <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.inspections.show', $inspection) }}">
                                        {{ __('ui.inspection.detail') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$inspections" />
        </div>
    @endif
@endsection
