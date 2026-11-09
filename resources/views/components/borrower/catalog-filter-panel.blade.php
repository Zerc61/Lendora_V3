@props(['categories', 'brands', 'locations', 'grades', 'accessLevels', 'filters'])

<div class="ldr-catalog-filter-panel" aria-label="{{ __('ui.catalog.filter_label') }}">
    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.asset.fields.category') }}</h3>
        <ul class="ldr-filter-list">
            <li>
                <button class="ldr-filter-option {{ ! $filters['category_id'] ? 'is-active' : '' }}" data-filter="category_id" data-value="">
                    {{ __('ui.common.all') }}
                </button>
            </li>
            @foreach ($categories->whereNull('parent_id') as $category)
                <li>
                    <button class="ldr-filter-option {{ $filters['category_id'] === $category->id ? 'is-active' : '' }}" data-filter="category_id" data-value="{{ $category->id }}">
                        {{ $category->name }}
                        <span class="ldr-filter-option__count">{{ isset($filters['_category_counts'][$category->id]) ? $filters['_category_counts'][$category->id] : 0 }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.catalog.availability') }}</h3>
        <ul class="ldr-filter-list">
            <li>
                <button class="ldr-filter-option {{ $filters['availability'] === 'available_now' ? 'is-active' : '' }}" data-filter="availability" data-value="available_now">
                    {{ __('ui.catalog.ready_now') }}
                </button>
            </li>
            <li>
                <button class="ldr-filter-option {{ $filters['availability'] === 'booked' ? 'is-active' : '' }}" data-filter="availability" data-value="booked">
                    {{ __('ui.catalog.in_use') }}
                </button>
            </li>
        </ul>
    </div>

    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.asset.fields.grade') }}</h3>
        <ul class="ldr-filter-list">
            @foreach ($grades as $grade => $label)
                <li>
                    <button class="ldr-filter-option {{ $filters['grade'] === $grade ? 'is-active' : '' }}" data-filter="grade" data-value="{{ $grade }}">
                        {{ $label }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.asset.fields.access') }}</h3>
        <ul class="ldr-filter-list">
            @foreach ($accessLevels as $level => $label)
                <li>
                    <button class="ldr-filter-option {{ $filters['access_level'] === $level ? 'is-active' : '' }}" data-filter="access_level" data-value="{{ $level }}">
                        {{ $label }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.catalog.fields.brand') }}</h3>
        <ul class="ldr-filter-list">
            @foreach ($brands as $brand)
                <li>
                    <button class="ldr-filter-option {{ $filters['brand'] === $brand ? 'is-active' : '' }}" data-filter="brand" data-value="{{ $brand }}">
                        {{ $brand }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="ldr-filter-section">
        <h3 class="ldr-filter-section__title">{{ __('ui.catalog.fields.location') }}</h3>
        <ul class="ldr-filter-list">
            @foreach ($locations as $location)
                <li>
                    <button class="ldr-filter-option {{ $filters['location'] === $location ? 'is-active' : '' }}" data-filter="location" data-value="{{ $location }}">
                        {{ $location }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="ldr-filter-actions">
        <button type="button" class="ldr-btn ldr-btn--ghost ldr-btn--sm" data-filter-reset>
            {{ __('ui.common.reset') }}
        </button>
        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--sm">
            {{ __('ui.common.apply') }}
        </button>
    </div>
</div>