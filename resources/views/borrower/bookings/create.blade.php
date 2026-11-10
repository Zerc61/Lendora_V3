@extends('layouts.borrower')

@section('title', __('ui.booking.new'))
@section('nav', 'catalog')

@section('content')
    <div class="ldr-request-page">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index') }}">{{ __('ui.nav.catalog') }}</a></li>
                <li aria-current="page">{{ __('ui.booking.form_title') }}</li>
            </ol>
        </nav>

        <header class="ldr-request-heading">
            <div>
                <span class="ldr-request-eyebrow">{{ __('ui.booking.new') }}</span>
                <h1>{{ __('ui.booking.form_title') }}</h1>
                <p>{{ __('ui.request_ui.intro') }}</p>
            </div>
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
                <x-icon name="chevron-left" :size="16" />
                {{ __('ui.common.back') }}
            </a>
        </header>

        <div class="ldr-request-layout">
            <form id="request-booking" method="POST" action="{{ route('borrower.bookings.store') }}" class="ldr-request-form" data-date-range data-max-days="{{ $maxLoanDays }}">
                @csrf

                @foreach ($assets as $asset)
                    <input type="hidden" name="assets[]" value="{{ $asset->id }}">
                @endforeach

                @if ($errors->any())
                    <div class="ldr-conflict-banner" role="alert">
                        <x-icon name="alert-triangle" :size="18" class="ldr-conflict-banner__icon" />
                        <div>
                            <p class="ldr-conflict-banner__title">{{ __('ui.booking.conflict_title') }}</p>
                            @foreach ($errors->all() as $message)
                                <p class="ldr-conflict-banner__message">{{ $message }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <section class="ldr-request-card" aria-labelledby="request-assets-title">
                    <div class="ldr-request-section-head">
                        <span class="ldr-request-step" aria-hidden="true">1</span>
                        <div>
                            <h2 id="request-assets-title">{{ __('ui.booking.selected_assets') }}</h2>
                            <p>{{ __('ui.request_ui.assets_hint') }}</p>
                        </div>
                    </div>

                    @if ($assets->isEmpty())
                        <div class="ldr-request-empty">
                            <p class="ldr-request-empty__title">{{ __('ui.booking.empty_selection_title') }}</p>
                            <p>{{ __('ui.booking.empty_selection_description') }}</p>
                            <a href="{{ route('borrower.catalog.index') }}" class="ldr-btn ldr-btn--secondary">
                                {{ __('ui.nav.catalog') }}
                            </a>
                        </div>
                    @else
                        <ul class="ldr-request-assets">
                            @foreach ($assets as $asset)
                                @php $assetPhoto = $asset->photos->first(); @endphp
                                <li class="ldr-request-asset">
                                    <div class="ldr-request-asset__image">
                                        @if ($assetPhoto)
                                            <img src="{{ Illuminate\Support\Facades\Storage::url($assetPhoto->path) }}" alt="{{ $asset->name }}" width="72" height="72">
                                        @else
                                            <x-icon name="camera" :size="24" />
                                        @endif
                                    </div>
                                    <div class="ldr-request-asset__info">
                                        <span class="ldr-request-asset__category">{{ $asset->category->name }}</span>
                                        <strong>{{ $asset->name }}</strong>
                                        <span class="ldr-mono">{{ $asset->code }}</span>
                                    </div>
                                    <x-status-badge :status="$asset->status->value" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="ldr-request-card" aria-labelledby="request-period-title">
                    <div class="ldr-request-section-head">
                        <span class="ldr-request-step" aria-hidden="true">2</span>
                        <div>
                            <h2 id="request-period-title">{{ __('ui.booking.period') }}</h2>
                            <p>{{ __('ui.request_ui.period_hint') }}</p>
                        </div>
                        <span class="ldr-request-duration" data-duration-display>—</span>
                    </div>

                    <div class="ldr-request-date-grid">
                        <div class="ldr-field">
                            <label class="ldr-field__label" for="requested_start_date">{{ __('ui.booking.start') }} <span aria-hidden="true">*</span></label>
                            <input type="date" name="requested_start_date" id="requested_start_date" class="ldr-input" data-date-start required value="{{ old('requested_start_date') }}" aria-describedby="request-date-help">
                            @error('requested_start_date') <p class="ldr-field-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="ldr-field">
                            <label class="ldr-field__label" for="requested_end_date">{{ __('ui.booking.end') }} <span aria-hidden="true">*</span></label>
                            <input type="date" name="requested_end_date" id="requested_end_date" class="ldr-input" data-date-end required value="{{ old('requested_end_date') }}">
                            @error('requested_end_date') <p class="ldr-field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="ldr-request-help" id="request-date-help">
                        <x-icon name="alert-circle" :size="16" />
                        {{ __('ui.booking.max_days_hint', ['days' => $maxLoanDays]) }}
                    </p>

                    @if ($conflict)
                        <div class="ldr-conflict-banner" role="alert">
                            <x-icon name="alert-triangle" :size="18" class="ldr-conflict-banner__icon" />
                            <div>
                                <p class="ldr-conflict-banner__title">{{ __('ui.booking.conflict_title') }}</p>
                                <p class="ldr-conflict-banner__message">{{ $conflict['asset_name'] }} — {{ $conflict['start'] }} s/d {{ $conflict['end'] }}</p>
                                <p class="ldr-conflict-banner__message">{{ __('ui.booking.conflict_code', ['code' => $conflict['booking_code']]) }}</p>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="ldr-request-card" aria-labelledby="request-purpose-title">
                    <div class="ldr-request-section-head">
                        <span class="ldr-request-step" aria-hidden="true">3</span>
                        <div>
                            <h2 id="request-purpose-title">{{ __('ui.booking.purpose') }}</h2>
                            <p>{{ __('ui.request_ui.purpose_hint') }}</p>
                        </div>
                    </div>
                    <div class="ldr-field">
                        <label class="ldr-field__label" for="purpose">{{ __('ui.booking.purpose') }} <span aria-hidden="true">*</span></label>
                        <textarea class="ldr-textarea" name="purpose" id="purpose" rows="4" placeholder="{{ __('ui.booking.purpose_placeholder') }}" required>{{ old('purpose') }}</textarea>
                        @error('purpose') <p class="ldr-field-error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <div class="ldr-request-mobile-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block" @disabled($assets->isEmpty())>
                        <x-icon name="arrow-right" :size="17" />
                        {{ __('ui.booking.submit') }}
                    </button>
                    <a href="{{ route('borrower.catalog.index') }}" class="ldr-btn ldr-btn--ghost ldr-btn--block">{{ __('ui.common.cancel') }}</a>
                </div>
            </form>

            <aside class="ldr-request-aside">
                <section class="ldr-request-aside-card">
                    <span class="ldr-request-aside-icon"><x-icon name="shield-check" :size="20" /></span>
                    <h2>{{ __('ui.request_ui.check_title') }}</h2>
                    <p>{{ __('ui.booking.rules') }}</p>
                </section>
                <section class="ldr-request-aside-card ldr-request-aside-card--tint">
                    <h2>{{ __('ui.request_ui.summary') }}</h2>
                    <dl class="ldr-request-summary">
                        <div><dt>{{ __('ui.request_ui.asset_count') }}</dt><dd>{{ $assets->count() }}</dd></div>
                        <div><dt>{{ __('ui.request_ui.duration_max') }}</dt><dd>{{ __('ui.booking.duration_days', ['count' => $maxLoanDays]) }}</dd></div>
                    </dl>
                    <p class="ldr-request-aside-note">{{ __('ui.request_ui.confirm_hint') }}</p>
                </section>
                <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block ldr-request-desktop-submit" form="request-booking" @disabled($assets->isEmpty())>
                    <x-icon name="arrow-right" :size="17" />
                    {{ __('ui.booking.submit') }}
                </button>
            </aside>
        </div>
    </div>
@endsection
