@props([
    'action',
    'filters' => [],
    'selects' => [],
    'searchPlaceholder' => null,
])

@php
    $hasFilters = collect($filters)->contains(fn ($value) => $value !== null && $value !== '');
@endphp

<form method="GET" action="{{ $action }}" class="ldr-filter-bar" role="search">
    <div class="ldr-filter-bar__search">
        <label class="ldr-sr-only" for="filter-search">{{ __('ui.common.search') }}</label>
        <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
        <input id="filter-search"
               class="ldr-input"
               type="search"
               name="search"
               value="{{ $filters['search'] ?? '' }}"
               placeholder="{{ $searchPlaceholder ?? __('ui.common.search') }}">
    </div>

    @foreach ($selects as $select)
        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="filter-{{ $select['name'] }}">{{ $select['label'] }}</label>
            <select id="filter-{{ $select['name'] }}" class="ldr-input" name="{{ $select['name'] }}">
                <option value="">{{ $select['placeholder'] ?? __('ui.common.all') }}</option>
                @foreach ($select['options'] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) ($filters[$select['name']] ?? '') === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endforeach

    <div class="ldr-filter-bar__actions">
        <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>

        @if ($hasFilters)
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ $action }}">{{ __('ui.common.reset') }}</a>
        @endif
    </div>
</form>
