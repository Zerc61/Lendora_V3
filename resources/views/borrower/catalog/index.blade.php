@extends('layouts.borrower')

@section('title', __('ui.nav.catalog'))
@section('nav', 'catalog')

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.catalog') }}</h1>
            <p class="ldr-muted">{{ __('ui.catalog.subtitle') }}</p>
        </div>

        <div class="ldr-catalog-summary" aria-label="{{ __('ui.catalog.availability') }}">
            <span class="ldr-catalog-summary__item is-available">
                <strong>{{ $statusCounts['available'] }}</strong>
                {{ __('ui.catalog.ready_now') }}
            </span>
            <span class="ldr-catalog-summary__item">
                <strong>{{ $statusCounts['borrowed'] + $statusCounts['reserved'] }}</strong>
                {{ __('ui.catalog.in_use') }}
            </span>
        </div>
    </div>

    <div class="ldr-catalog-search">
        <form method="GET" action="{{ route('borrower.catalog.index') }}" role="search">
            <label class="ldr-sr-only" for="catalog-search">{{ __('ui.catalog.search_placeholder') }}</label>
            <div class="ldr-catalog-search__input">
                <x-icon name="search" :size="18" />
                <input id="catalog-search"
                       class="ldr-input"
                       type="search"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="{{ __('ui.catalog.search_placeholder') }}">
            </div>

            @if ($filters['category_id'])
                <input type="hidden" name="category_id" value="{{ $filters['category_id'] }}">
            @endif

            @if ($filters['start_date'])
                <input type="hidden" name="start_date" value="{{ $filters['start_date']->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ $filters['end_date']->format('Y-m-d') }}">
            @endif

            @if ($filters['availability'])
                <input type="hidden" name="availability" value="{{ $filters['availability'] }}">
            @endif

            @if ($filters['brand'])
                <input type="hidden" name="brand" value="{{ $filters['brand'] }}">
            @endif

            @if ($filters['location'])
                <input type="hidden" name="location" value="{{ $filters['location'] }}">
            @endif

            @if ($filters['grade'])
                <input type="hidden" name="grade" value="{{ $filters['grade'] }}">
            @endif

            @if ($filters['access_level'])
                <input type="hidden" name="access_level" value="{{ $filters['access_level'] }}">
            @endif

            @if ($filters['sort'])
                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            @endif
        </form>

        <div class="ldr-chip-row" role="list" aria-label="{{ __('ui.asset.fields.category') }}">
            <a class="ldr-chip-toggle {{ ! $filters['category_id'] ? 'is-active' : '' }}"
               href="{{ route('borrower.catalog.index', array_filter(['search' => $filters['search']])) }}"
               role="listitem">
                {{ __('ui.common.all') }}
            </a>

            @foreach ($categories->whereNull('parent_id') as $category)
                <a class="ldr-chip-toggle {{ $filters['category_id'] === $category->id ? 'is-active' : '' }}"
                   href="{{ route('borrower.catalog.index', array_filter(['search' => $filters['search'], 'category_id' => $category->id])) }}"
                   role="listitem">
                    {{ $category->name }}
                    @if (isset($facets['categories'][$category->id]))
                        <span class="ldr-chip-toggle__count">{{ $facets['categories'][$category->id] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    @if ($activeFilters = collect($activeFilters)->except('sort')->filter())
        <div class="ldr-active-filters">
            @foreach ($activeFilters as $key => $value)
                <span class="ldr-filter-chip">
                    {{ $value }}
                    <a href="{{ route('borrower.catalog.index', collect($activeFilters)->except($key)->toArray()) }}" class="ldr-filter-chip__remove" aria-label="{{ __('ui.common.remove_filter', ['filter' => $value]) }}">
                        <x-icon name="x" :size="12" />
                    </a>
                </span>
            @endforeach
            <a href="{{ route('borrower.catalog.index') }}" class="ldr-btn ldr-btn--ghost ldr-btn--sm">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    @endif

    <div class="ldr-catalog-toolbar">
        <p class="ldr-catalog-count">{{ __('ui.catalog.result_count', ['count' => $assets->total()]) }}</p>

        <div class="ldr-catalog-sort">
            <label for="catalog-sort" class="ldr-sr-only">{{ __('ui.catalog.sort_label') }}</label>
            <select id="catalog-sort" class="ldr-select" onchange="this.form.submit()">
                <option value="availability" {{ $filters['sort'] === 'availability' ? 'selected' : '' }}>{{ __('ui.catalog.sort_availability') }}</option>
                <option value="name" {{ $filters['sort'] === 'name' ? 'selected' : '' }}>{{ __('ui.catalog.sort_name') }}</option>
                <option value="popular" {{ $filters['sort'] === 'popular' ? 'selected' : '' }}>{{ __('ui.catalog.sort_popular') }}</option>
                <option value="newest" {{ $filters['sort'] === 'newest' ? 'selected' : '' }}>{{ __('ui.catalog.sort_newest') }}</option>
            </select>
        </div>
    </div>

    @if ($assets->isEmpty())
        <x-empty-state
            icon="search"
            :title="__('ui.catalog.empty_title')"
            :description="__('ui.catalog.empty_description')"
        >
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </x-empty-state>
    @else
        <div class="ldr-asset-grid">
            @foreach ($assets as $asset)
                <x-asset-card
                    :asset="$asset"
                    :href="route('borrower.catalog.show', $asset)"
                    :availabilityByAsset="$availabilityByAsset[$asset->id] ?? []"
                    :favorite="$asset->favorites->contains('user_id', auth()->id())"
                />
            @endforeach
        </div>

        <x-pagination :paginator="$assets" />
    @endif
@endsection