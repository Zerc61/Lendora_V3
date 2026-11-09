@props(['asset', 'href', 'availabilityByAsset' => [], 'favorite' => false])

@php
    use App\Enums\AssetStatus;
    $photo = $asset->photos->first();
    $availableCount = $asset->status === AssetStatus::Available ? $asset->available_units ?? 1 : 0;
    $gradeValue = $asset->grade ? (is_string($asset->grade) ? strtolower($asset->grade) : strtolower($asset->grade->value)) : null;
@endphp

<article class="ldr-asset-card {{ $favorite ? 'ldr-asset-card--favorited' : '' }}">
    <a class="ldr-asset-card__media" href="{{ $href }}">
        @if ($photo)
            <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                 alt="{{ $asset->name }}"
                 loading="lazy"
                 width="400"
                 height="300">
        @else
            <span class="ldr-asset-card__placeholder" aria-hidden="true">
                <x-icon name="camera" :size="32" />
            </span>
        @endif

        <x-status-badge :status="$asset->status->value" class="ldr-asset-card__status" />
    </a>

    <button type="button" class="ldr-favorite-btn {{ $favorite ? 'is-active' : '' }}"
            data-asset="{{ $asset->id }}"
            data-favorite-toggle
            aria-label="{{ $favorite ? __('ui.common.remove_from_favorites') : __('ui.common.add_to_favorites') }}">
        <x-icon name="heart" :size="18" />
    </button>

    <div class="ldr-asset-card__body">
        <div class="ldr-asset-card__tags">
            <span class="ldr-chip">{{ $asset->category->name }}</span>
            @if ($gradeValue)
                <span class="ldr-badge ldr-badge--grade ldr-badge--grade-{{ $gradeValue }}">
                    {{ __('ui.asset.grade.' . $gradeValue) }}
                </span>
            @endif
        </div>

        <h3 class="ldr-asset-card__title">
            <a href="{{ $href }}">{{ $asset->name }}</a>
        </h3>

        @if ($asset->brand || $asset->model)
            <p class="ldr-asset-card__brand">
                {{ collect([$asset->brand, $asset->model])->filter()->join(' · ') }}
            </p>
        @endif

        <p class="ldr-asset-card__code">{{ $asset->code }}</p>

        @if ($asset->description)
            <p class="ldr-asset-card__desc">{{ Str::limit($asset->description, 96) }}</p>
        @endif

        <p class="ldr-asset-card__meta">
            {{ __('ui.condition.'.$asset->condition_status->value) }}
            @if ($asset->location)
                · {{ $asset->location }}
            @endif
        </p>

        <div class="ldr-asset-card__availability-row">
            <span class="ldr-available-count">
                <strong>{{ $availableCount }}</strong> {{ __('ui.asset.available_units') }}
            </span>

            @if (! empty($availabilityByAsset))
                <span class="ldr-availability-strip" aria-label="{{ __('ui.catalog.availability') }}">
                    @foreach ($availabilityByAsset as $day)
                        <span class="ldr-availability-dot {{ $day['status'] === 'available' ? 'is-available' : 'is-unavailable' }}"
                              title="{{ $day['date']->format('d M') }}"></span>
                    @endforeach
                </span>
            @endif
        </div>

        <a class="ldr-asset-card__cta {{ $asset->status === AssetStatus::Available ? 'ldr-asset-card__cta--available' : 'ldr-asset-card__cta--unavailable' }}"
           href="{{ $href }}">
            {{ $asset->status === AssetStatus::Available ? __('ui.catalog.apply') : __('ui.catalog.apply_unavailable') }}
        </a>
    </div>
</article>