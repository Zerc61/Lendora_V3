@props(['asset', 'href'])

@php
    $photo = $asset->photos->first();
@endphp

<article class="ldr-asset-card">
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

    <div class="ldr-asset-card__body">
        <span class="ldr-chip">{{ $asset->category->name }}</span>

        <h3 class="ldr-asset-card__title">
            <a href="{{ $href }}">{{ $asset->name }}</a>
        </h3>

        <p class="ldr-asset-card__code">{{ $asset->code }}</p>

        <p class="ldr-asset-card__meta">
            {{ __('ui.condition.'.$asset->condition_status->value) }}
            @if ($asset->location)
                · {{ $asset->location }}
            @endif
        </p>
    </div>
</article>
