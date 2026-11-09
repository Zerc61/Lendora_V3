@extends('layouts.borrower')

@section('title', $asset->name)
@section('nav', 'catalog')

@section('content')
    @php
        use App\Enums\AssetStatus;
    @endphp
    <div class="ldr-detail">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index') }}">{{ __('ui.nav.catalog') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index', ['category_id' => $asset->category_id]) }}">{{ $asset->category->name }}</a></li>
                <li aria-current="page">{{ $asset->name }}</li>
            </ol>
        </nav>

        <div class="ldr-detail__layout">
            <div class="ldr-detail__media">
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

@php
    $gradeValue = $asset->grade ? (is_string($asset->grade) ? strtolower($asset->grade) : strtolower($asset->grade->value)) : null;
@endphp

                @if ($gradeValue)
                    <div class="ldr-grade-badge">
                        <span class="ldr-badge ldr-badge--grade ldr-badge--grade-{{ $gradeValue }}">
                            {{ __('ui.asset.grade.' . $gradeValue) }}
                        </span>
                    </div>
                @endif

                @if ($favorite === false)
                    <button type="button" class="ldr-favorite-float" data-asset="{{ $asset->id }}" data-favorite-toggle aria-label="{{ __('ui.common.add_to_favorites') }}">
                        <x-icon name="heart" :size="20" />
                    </button>
                @endif
            </div>

            <div class="ldr-detail__info">
                <header class="ldr-detail__head">
                    <span class="ldr-chip">{{ $asset->category->name }}</span>
                    <h1>{{ $asset->name }}</h1>
                    <p class="ldr-detail__brand">
                        {{ collect([$asset->brand, $asset->model])->filter()->join(' · ') ?: '—' }}
                    </p>
                    <p class="ldr-mono ldr-muted">{{ $asset->code }}</p>

                    <div class="ldr-detail__badges">
                        <x-status-badge :status="$asset->status->value" />
                        <x-condition-badge :condition="$asset->condition_status->value" />
                    </div>
                </header>

                <div class="ldr-detail__actions">
                    <a href="{{ route('borrower.bookings.create', ['assets' => [$asset->id]]) }}" class="ldr-btn ldr-btn--primary ldr-btn--block {{ $isAvailable ? '' : 'ldr-btn--disabled' }} {{ !$isAvailable ? 'disabled' : '' }}">
                        <x-icon name="calendar" :size="16" />
                        {{ $isAvailable ? __('ui.catalog.apply') : __('ui.catalog.apply_unavailable') }}
                    </a>

                    @if ($isAvailable)
                        <a href="#availability-calendar" class="ldr-btn ldr-btn--secondary ldr-btn--block">
                            {{ __('ui.catalog.check_availability') }}
                        </a>
                    @endif
                </div>

                @if ($asset->description)
                    <section class="ldr-card">
                        <h2>{{ __('ui.catalog.about') }}</h2>
                        <p class="ldr-detail__description">{{ $asset->description }}</p>
                    </section>
                @endif

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

                        @foreach ($asset->specifications ?? [] as $label => $value)
                            <div>
                                <dt>{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                @if ($asset->components->isNotEmpty())
                    <section class="ldr-card">
                        <h2>{{ __('ui.asset.bundle') }}</h2>
                        <x-borrower.asset-bundle-list :components="$asset->components" />
                    </section>
                @endif

                @if ($asset->qcChecks->isNotEmpty())
                    <section class="ldr-card">
                        <h2>{{ __('ui.asset.qc_history') }}</h2>
                        <x-borrower.asset-qc-card :checks="$asset->qcChecks" />
                    </section>
                @endif

                <section class="ldr-card" id="availability-calendar">
                    <h2>{{ __('ui.catalog.availability_30') }}</h2>

                    <div class="ldr-availability">
                        @foreach ($availabilityDays as $day)
                            <div class="ldr-availability__day {{ $day['status'] === 'available' ? 'is-available' : 'is-unavailable' }}"
                                 role="listitem"
                                 title="{{ $day['label'] ?? __('ui.catalog.availability_available') }}">
                                <span class="ldr-availability__weekday">{{ $day['date']->translatedFormat('D') }}</span>
                                <span class="ldr-availability__date">{{ $day['date']->format('j') }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                @if ($compatibleAddons->isNotEmpty())
                    <section class="ldr-card">
                        <h2>{{ __('ui.asset.compatible_addons') }}</h2>
                        <div class="ldr-addon-grid">
                            @foreach ($compatibleAddons as $addon)
                                <article class="ldr-addon-card">
                                    <a href="{{ route('borrower.catalog.show', $addon) }}">
                                        <span class="ldr-addon-card__name">{{ $addon->name }}</span>
                                        <span class="ldr-addon-card__code">{{ $addon->code }}</span>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endsection