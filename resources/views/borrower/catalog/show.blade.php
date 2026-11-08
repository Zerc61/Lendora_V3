@extends('layouts.borrower')

@section('title', $asset->name)
@section('nav', 'catalog')

@section('content')
    <div class="ldr-detail">
        <a class="ldr-back-link" href="{{ route('borrower.catalog.index') }}">
            <x-icon name="chevron-left" :size="18" />
            {{ __('ui.catalog.back_to_catalog') }}
        </a>

        <div class="ldr-gallery">
            @if ($asset->photos->isNotEmpty())
                <div class="ldr-gallery__track">
                    @foreach ($asset->photos as $photo)
                        <figure class="ldr-gallery__slide" id="photo-{{ $loop->iteration }}">
                            <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                 alt="{{ $asset->name }} — {{ $loop->iteration }}"
                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                 width="480"
                                 height="360">
                        </figure>
                    @endforeach
                </div>

                @if ($asset->photos->count() > 1)
                    <div class="ldr-gallery__dots" aria-hidden="true">
                        @foreach ($asset->photos as $photo)
                            <a href="#photo-{{ $loop->iteration }}" class="ldr-gallery__dot"></a>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="ldr-gallery__empty">
                    <x-icon name="camera" :size="40" />
                    <span>{{ __('ui.catalog.no_photo') }}</span>
                </div>
            @endif
        </div>

        <header class="ldr-detail__head">
            <span class="ldr-chip">{{ $asset->category->name }}</span>
            <h1>{{ $asset->name }}</h1>
            <p class="ldr-mono ldr-muted">{{ $asset->code }}</p>

            <div class="ldr-detail__badges">
                <x-status-badge :status="$asset->status->value" />
                <x-condition-badge :condition="$asset->condition_status->value" />
            </div>
        </header>

        <section class="ldr-card">
            <h2>{{ __('ui.catalog.specs') }}</h2>

            <dl class="ldr-spec-list">
                <div>
                    <dt>{{ __('ui.catalog.fields.brand') }}</dt>
                    <dd>{{ $asset->brand ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.catalog.fields.model') }}</dt>
                    <dd>{{ $asset->model ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.catalog.fields.location') }}</dt>
                    <dd>{{ $asset->location ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.catalog.fields.description') }}</dt>
                    <dd>{{ $asset->description ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="ldr-card">
            <h2>{{ __('ui.catalog.availability') }}</h2>

            <div class="ldr-availability" role="list">
                @foreach ($availabilityDays as $day)
                    <div class="ldr-availability__day {{ $day['available'] ? 'is-available' : 'is-unavailable' }}"
                         role="listitem"
                         title="{{ $day['date']->translatedFormat('d M Y') }}">
                        <span class="ldr-availability__weekday">{{ $day['date']->translatedFormat('D') }}</span>
                        <span class="ldr-availability__date">{{ $day['date']->format('j') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="ldr-availability__legend">
                <span><i class="is-available"></i> {{ __('ui.catalog.availability_available') }}</span>
                <span><i class="is-unavailable"></i> {{ __('ui.catalog.availability_unavailable') }}</span>
            </div>
        </section>

        <div class="ldr-detail-cta">
            @if ($isAvailable)
                <a class="ldr-btn ldr-btn--primary ldr-btn--block"
                   href="{{ route('borrower.bookings.create', ['assets' => [$asset->id]]) }}">
                    {{ __('ui.catalog.apply') }}
                </a>
            @else
                <button type="button" class="ldr-btn ldr-btn--primary ldr-btn--block" disabled>
                    {{ __('ui.catalog.apply') }}
                </button>
                <p class="ldr-detail-cta__reason">
                    {{ $asset->status === App\Enums\AssetStatus::Reserved
                        ? __('ui.catalog.apply_reserved')
                        : __('ui.catalog.apply_unavailable') }}
                </p>
            @endif
        </div>
    </div>
@endsection
