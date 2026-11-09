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
                </a>
            @endforeach
        </div>
    </div>

    <p class="ldr-catalog-count">{{ __('ui.catalog.result_count', ['count' => $assets->total()]) }}</p>

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
                <x-asset-card :asset="$asset" :href="route('borrower.catalog.show', $asset)" />
            @endforeach
        </div>

        <x-pagination :paginator="$assets" />
    @endif
@endsection
