@extends('layouts.admin')

@section('title', __('ui.nav.inspections'))

@section('content')
    <div class="ldr-page-head">
        <div>
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
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.inspection.checked_at') }}</th>
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th>{{ __('ui.inspection.result') }}</th>
                            <th>{{ __('ui.inspection.inspector') }}</th>
                            <th>{{ __('ui.booking.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inspections as $inspection)
                            <tr>
                                <td class="ldr-table__num">
                                    {{ $inspection->checked_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $inspection->asset?->name }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $inspection->asset?->code }}</span>
                                </td>
                                <td>
                                    <span class="ldr-badge {{ $inspection->result === App\Enums\InspectionResult::Ok ? 'ldr-badge--success' : ($inspection->result === App\Enums\InspectionResult::NeedsRepair ? 'ldr-badge--warning' : 'ldr-badge--danger') }}">
                                        {{ __('ui.inspection_result.'.$inspection->result->value) }}
                                    </span>
                                </td>
                                <td>{{ $inspection->inspector?->name ?? '—' }}</td>
                                <td>{{ $inspection->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$inspections" />
        </div>
    @endif
@endsection
