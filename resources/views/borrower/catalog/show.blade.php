@extends('layouts.borrower')

@section('title', $asset->name)
@section('nav', 'catalog')

@section('content')
    @php
        $gradeValue = $asset->grade ? strtolower(is_string($asset->grade) ? $asset->grade : $asset->grade->value) : null;
    @endphp

    <div class="ldr-asset-page">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index') }}">{{ __('ui.nav.catalog') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index', ['category_id' => $asset->category_id]) }}">{{ $asset->category->name }}</a></li>
                <li aria-current="page">{{ $asset->name }}</li>
            </ol>
        </nav>

        <div class="ldr-asset-layout">
        <section class="ldr-asset-gallery" aria-label="{{ __('ui.asset_detail.gallery_label', ['name' => $asset->name]) }}">
                <div class="ldr-asset-gallery__hero">
                    @if ($asset->photos->isNotEmpty())
                        <div class="ldr-asset-gallery__track">
                            @foreach ($asset->photos as $photo)
                                <figure id="asset-photo-{{ $loop->iteration }}">
                                    <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}" alt="{{ __('ui.asset_detail.photo_alt', ['name' => $asset->name, 'number' => $loop->iteration]) }}" width="960" height="640" loading="{{ $loop->first ? 'eager' : 'lazy' }}" @if ($loop->first) fetchpriority="high" @endif>
                                </figure>
                            @endforeach
                        </div>
                    @else
                        <div class="ldr-asset-gallery__empty"><x-icon name="camera" :size="42" /><span>{{ __('ui.catalog.no_photo') }}</span></div>
                    @endif
                    @if ($asset->photos->isNotEmpty())
                        <span class="ldr-asset-gallery__counter"><x-icon name="camera" :size="14" /> {{ __('ui.asset_detail.photo_count', ['count' => $asset->photos->count()]) }}</span>
                    @endif
                    @if ($gradeValue)
                        <span class="ldr-asset-gallery__grade">{{ __('ui.asset.grade.' . $gradeValue) }}</span>
                    @endif
                </div>
                @if ($asset->photos->count() > 1)
                    <div class="ldr-asset-gallery__thumbs" aria-label="{{ __('ui.asset_detail.more_photos') }}">
                        @foreach ($asset->photos as $photo)
                            <a href="#asset-photo-{{ $loop->iteration }}" aria-label="{{ __('ui.asset_detail.photo_link', ['number' => $loop->iteration]) }}">
                                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}" alt="{{ __('ui.asset_detail.photo_alt', ['name' => $asset->name, 'number' => $loop->iteration]) }}" width="120" height="90" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside class="ldr-asset-summary">
                <div class="ldr-asset-summary__top">
                    <span class="ldr-mono ldr-asset-code">{{ $asset->code }}</span>
                    <x-status-badge :status="$asset->status->value" />
                </div>
                <span class="ldr-chip">{{ $asset->category->name }}</span>
                <h1>{{ $asset->name }}</h1>
                <p class="ldr-asset-summary__model">{{ collect([$asset->brand, $asset->model])->filter()->join(' · ') ?: '—' }}</p>

                <div class="ldr-asset-summary__badges">
                    <x-condition-badge :condition="$asset->condition_status->value" />
                    @if ($rating['count'] > 0)
                        <span class="ldr-asset-rating"><span aria-hidden="true">★</span> {{ number_format($rating['average'], 1) }} <small>({{ $rating['count'] }})</small></span>
                    @endif
                </div>

                @if ($asset->location)
                    <div class="ldr-asset-location">
                        <span class="ldr-asset-location__icon"><x-icon name="package" :size="18" /></span>
                        <div><span class="ldr-request-eyebrow">{{ __('ui.asset_detail.location_label') }}</span><strong>{{ $asset->location }}</strong></div>
                    </div>
                @endif

                <div class="ldr-asset-summary__actions">
                    @if ($isAvailable)
                        <a href="{{ route('borrower.bookings.create', ['assets' => [$asset->id]]) }}" class="ldr-btn ldr-btn--primary ldr-btn--block">
                            <x-icon name="calendar" :size="17" /> {{ __('ui.catalog.apply') }}
                        </a>
                    @else
                        <span class="ldr-btn ldr-btn--primary ldr-btn--block is-disabled" aria-disabled="true">
                            <x-icon name="calendar" :size="17" /> {{ __('ui.catalog.apply_unavailable') }}
                        </span>
                    @endif
                    <a href="#asset-availability" class="ldr-btn ldr-btn--secondary ldr-btn--block">{{ __('ui.catalog.check_availability') }}</a>
                </div>
                <p class="ldr-asset-summary__note">{{ __('ui.asset_detail.availability_note') }}</p>
            </aside>
        </div>

        <div class="ldr-asset-details">
            <div class="ldr-asset-details__main">
                <section class="ldr-asset-panel" id="asset-availability">
                    <div class="ldr-asset-panel__heading">
                        <div>
                            <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.schedule_label') }}</span>
                            <h2><span class="ldr-availability-title--desktop">{{ __('ui.catalog.availability_30') }}</span><span class="ldr-availability-title--mobile">{{ __('ui.asset_detail.availability_14') }}</span></h2>
                        </div>
                        <span class="ldr-availability-legend"><i></i>{{ __('ui.catalog.availability_available') }} <i class="is-busy"></i>{{ __('ui.catalog.availability_unavailable') }}</span>
                    </div>
                    <div class="ldr-availability" role="list" aria-label="{{ __('ui.asset_detail.availability_label') }}">
                        @foreach ($availabilityDays as $day)
                            <div class="ldr-availability__day {{ $day['status'] === 'available' ? 'is-available' : 'is-unavailable' }}" role="listitem" title="{{ $day['label'] ?? __('ui.catalog.availability_available') }}">
                                <span class="ldr-availability__weekday">{{ $day['date']->translatedFormat('D') }}</span>
                                <span class="ldr-availability__date">{{ $day['date']->format('j') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="ldr-asset-panel__hint">{{ __('ui.asset_detail.calendar_hint') }}</p>
                </section>

                <section class="ldr-asset-panel">
                    <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.information_label') }}</span>
                    <h2>{{ __('ui.catalog.specs') }}</h2>
                    <dl class="ldr-asset-specs">
                        <div><dt>{{ __('ui.catalog.fields.brand') }}</dt><dd>{{ $asset->brand ?: '—' }}</dd></div>
                        <div><dt>{{ __('ui.catalog.fields.model') }}</dt><dd>{{ $asset->model ?: '—' }}</dd></div>
                        <div><dt>{{ __('ui.catalog.fields.location') }}</dt><dd>{{ $asset->location ?: '—' }}</dd></div>
                        @foreach ($asset->specifications ?? [] as $label => $value)
                            <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </section>

                @if ($asset->components->isNotEmpty())
                    <section class="ldr-asset-panel">
                        <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.bundle_label') }}</span>
                        <h2>{{ __('ui.asset.bundle') }}</h2>
                        <x-borrower.asset-bundle-list :components="$asset->components" />
                    </section>
                @endif

                @if ($asset->qcChecks->isNotEmpty())
                    <section class="ldr-asset-panel">
                        <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.qc_label') }}</span>
                        <h2>{{ __('ui.asset.qc_history') }}</h2>
                        <x-borrower.asset-qc-card :checks="$asset->qcChecks" />
                    </section>
                @endif

                @if ($asset->description)
                    <section class="ldr-asset-panel">
                        <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.about_label') }}</span>
                        <h2>{{ __('ui.catalog.about') }}</h2>
                        <p class="ldr-asset-description">{{ $asset->description }}</p>
                    </section>
                @endif
            </div>

            @if ($compatibleAddons->isNotEmpty())
                <aside class="ldr-asset-panel ldr-asset-compatible">
                    <span class="ldr-request-eyebrow">{{ __('ui.asset_detail.compatible_label') }}</span>
                    <h2>{{ __('ui.asset.compatible_addons') }}</h2>
                    <div class="ldr-addon-grid">
                        @foreach ($compatibleAddons as $addon)
                            <a class="ldr-addon-card" href="{{ route('borrower.catalog.show', $addon) }}">
                                <span class="ldr-addon-card__name">{{ $addon->name }}</span>
                                <span class="ldr-addon-card__code">{{ $addon->code }}</span>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>

        <div class="ldr-asset-mobile-cta">
            @if ($isAvailable)
                <a href="{{ route('borrower.bookings.create', ['assets' => [$asset->id]]) }}" class="ldr-btn ldr-btn--primary ldr-btn--block">
                    <x-icon name="calendar" :size="17" /> {{ __('ui.catalog.apply') }}
                </a>
            @else
                <span class="ldr-btn ldr-btn--primary ldr-btn--block is-disabled" aria-disabled="true">
                    <x-icon name="calendar" :size="17" /> {{ __('ui.catalog.apply_unavailable') }}
                </span>
            @endif
            <span>{{ __('ui.asset_detail.availability_note') }}</span>
        </div>
    </div>
@endsection
