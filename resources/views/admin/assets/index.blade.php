@extends('layouts.admin')

@section('title', __('ui.nav.assets'))

@php
    $canManage = auth()->user()->hasRole('organization_admin');

    $categoryOptions = collect($categories)->map(fn (array $category) => [
        'value' => $category['id'],
        'label' => str_repeat('— ', $category['depth']).$category['name'],
    ])->all();

    $exportQuery = array_filter([
        'search' => $filters['search'] ?? null,
        'category_id' => $filters['category_id'] ?? null,
        'status' => $filters['status'] ?? null,
        'condition_status' => $filters['condition_status'] ?? null,
        'location' => $filters['location'] ?? null,
    ]);

    $activeFilters = array_filter([
        $filters['search'] ? __('ui.common.search').': '.$filters['search'] : null,
        $filters['status'] ? __('ui.asset.fields.status').': '.__('ui.status.'.$filters['status']) : null,
        $filters['condition_status'] ? __('ui.asset.fields.condition_status').': '.__('ui.condition.'.$filters['condition_status']) : null,
        $filters['location'] ? __('ui.asset.fields.location').': '.$filters['location'] : null,
    ]);
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W3 · {{ __('ui.nav.assets') }}</span>
            <h1>{{ __('ui.nav.assets') }}</h1>
            <p class="ldr-muted">{{ __('ui.asset.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.assets.export', $exportQuery) }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.asset.export') }}
            </a>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.assets.export', $exportQuery + ['format' => 'xlsx']) }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.common.download_xlsx') }}
            </a>

            @if ($canManage)
                <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.assets.create') }}">
                    <x-icon name="plus" :size="18" />
                    {{ __('ui.asset.add') }}
                </a>
            @endif
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.asset.kpi_total') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="package" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['total'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.asset.kpi_total_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.status.available') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['available'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.asset.kpi_available_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.status.borrowed') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="layers" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['borrowed'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.asset.kpi_borrowed_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.status.maintenance') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="wrench" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['maintenance'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.asset.kpi_maintenance_hint') }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.assets.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="asset-search">{{ __('ui.asset.search_placeholder') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="asset-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.asset.search_placeholder') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="asset-category">{{ __('ui.asset.fields.category') }}</label>
            <select id="asset-category" class="ldr-input" name="category_id">
                <option value="">{{ __('ui.asset.all_categories') }}</option>
                @foreach ($categoryOptions as $option)
                    <option value="{{ $option['value'] }}" @selected((int) ($filters['category_id'] ?? 0) === (int) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="asset-status">{{ __('ui.asset.fields.status') }}</label>
            <select id="asset-status" class="ldr-input" name="status">
                <option value="">{{ __('ui.common.all') }}</option>
                @foreach (App\Enums\AssetStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ __('ui.status.'.$status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="asset-condition">{{ __('ui.asset.fields.condition_status') }}</label>
            <select id="asset-condition" class="ldr-input" name="condition_status">
                <option value="">{{ __('ui.asset.all_conditions') }}</option>
                @foreach (App\Enums\AssetCondition::cases() as $condition)
                    <option value="{{ $condition->value }}" @selected(($filters['condition_status'] ?? null) === $condition->value)>
                        {{ __('ui.condition.'.$condition->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="asset-location">{{ __('ui.asset.fields.location') }}</label>
            <select id="asset-location" class="ldr-input" name="location">
                <option value="">{{ __('ui.asset.all_locations') }}</option>
                @foreach ($locations as $location)
                    <option value="{{ $location }}" @selected(($filters['location'] ?? null) === $location)>{{ $location }}</option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.assets.index') }}">{{ __('ui.common.reset') }}</a>
        </div>
    </form>

    @if ($activeFilters !== [])
        <div class="ldr-filter-chips">
            <span class="ldr-filter-chips__label">{{ __('ui.asset.active_filters') }}</span>
            @foreach ($activeFilters as $chip)
                <span class="ldr-filter-chip">{{ $chip }}</span>
            @endforeach
            <a class="ldr-filter-chips__reset" href="{{ route('admin.assets.index') }}">{{ __('ui.asset.reset_filters') }}</a>
        </div>
    @endif

    @if ($assets->isEmpty())
        <x-empty-state
            icon="package"
            :title="__('ui.asset.empty_title')"
            :description="__('ui.asset.empty_description')"
        >
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.assets.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </x-empty-state>
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table ldr-asset-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.asset.column_photo') }}</th>
                            <th>{{ __('ui.asset.fields.name') }}</th>
                            <th>{{ __('ui.asset.fields.category') }}</th>
                            <th>{{ __('ui.asset.fields.status') }}</th>
                            <th>{{ __('ui.asset.fields.condition_status') }}</th>
                            <th>{{ __('ui.asset.fields.location') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            @php($photo = $asset->photos->first())

                            <tr>
                                <td>
                                    <div class="ldr-asset-cell">
                                        <span class="ldr-thumb">
                                            @if ($photo)
                                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                                     alt="{{ $asset->name }}"
                                                     loading="lazy"
                                                     width="48"
                                                     height="48">
                                            @else
                                                <x-icon name="camera" :size="20" />
                                            @endif
                                        </span>
                                        <div>
                                            <span class="ldr-table__code ldr-mono">{{ $asset->code }}</span>
                                            <span class="ldr-table__sub">{{ $asset->serial_number ?: '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $asset->name }}</span>
                                    <span class="ldr-table__sub">
                                        {{ collect([$asset->brand, $asset->model])->filter()->join(' · ') ?: '—' }}
                                    </span>
                                </td>
                                <td>{{ $asset->category->name }}</td>
                                <td><x-status-badge :status="$asset->status->value" /></td>
                                <td><x-condition-badge :condition="$asset->condition_status->value" /></td>
                                <td>{{ $asset->location ?: '—' }}</td>
                                <td>
                                    <div class="ldr-table__actions">
                                        @if ($canManage)
                                            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                               href="{{ route('admin.assets.edit', $asset) }}">
                                                {{ __('ui.common.edit') }}
                                            </a>

                                            @if ($asset->status !== App\Enums\AssetStatus::Retired)
                                                <button type="button"
                                                        class="ldr-icon-btn ldr-icon-btn--danger"
                                                        data-confirm-open="retire-asset-{{ $asset->id }}"
                                                        title="{{ __('ui.asset.retire') }}"
                                                        aria-label="{{ __('ui.asset.retire') }} {{ $asset->code }}">
                                                    <x-icon name="x" :size="18" />
                                                </button>
                                            @endif
                                        @else
                                            <span class="ldr-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$assets" />
        </div>
    @endif

    @if ($canManage)
        @foreach ($assets as $asset)
            @if ($asset->status !== App\Enums\AssetStatus::Retired)
                <x-confirm-modal
                    :id="'retire-asset-'.$asset->id"
                    :title="__('ui.asset.retire_title')"
                    :message="__('ui.asset.retire_message', ['code' => $asset->code])"
                    :action="route('admin.assets.retire', $asset)"
                    :confirm-label="__('ui.asset.retire')"
                    variant="danger"
                />
            @endif
        @endforeach
    @endif
@endsection
