@extends('layouts.borrower')

@section('title', __('ui.booking.new'))
@section('nav', 'catalog')

@section('content')
    <div class="ldr-booking-create">
        <nav class="ldr-breadcrumbs" aria-label="{{ __('ui.breadcrumb') }}">
            <ol class="ldr-breadcrumbs__list">
                <li><a href="{{ route('borrower.home') }}">{{ __('ui.nav.home') }}</a></li>
                <li><a href="{{ route('borrower.catalog.index') }}">{{ __('ui.nav.catalog') }}</a></li>
                <li aria-current="page">{{ __('ui.booking.form_title') }}</li>
            </ol>
        </nav>

        <div class="ldr-booking-create__grid">
            <aside class="ldr-card">
                <h2 class="ldr-section-title">{{ __('ui.booking.selected_assets') }}</h2>

                @if ($assets->isEmpty())
                    <p class="ldr-muted">{{ __('ui.booking.empty_selection_title') }}</p>
                    <p class="ldr-form-hint">{{ __('ui.booking.empty_selection_description') }}</p>
                    <a href="{{ route('borrower.catalog.index') }}" class="ldr-btn ldr-btn--secondary ldr-btn--block">
                        {{ __('ui.nav.catalog') }}
                    </a>
                @else
                    <ul class="ldr-booking-items">
                        @foreach ($assets as $asset)
                            <li class="ldr-booking-items__row">
                                <div class="ldr-booking-items__main">
                                    <span class="ldr-booking-items__name">{{ $asset->name }}</span>
                                    <span class="ldr-muted">{{ $asset->code }}</span>
                                </div>
                                <span class="ldr-badge">{{ $asset->category->name }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>


            <form method="POST" action="{{ route('borrower.bookings.store') }}" class="ldr-booking-form" data-date-range data-max-days="{{ $maxLoanDays }}">
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

                <div class="ldr-field">
                    <label class="ldr-field__label">{{ __('ui.asset.fields.category') }}</label>
                    <select class="ldr-select" name="category_id" id="category_id">
                        <option value="">{{ __('ui.common.all') }}</option>
                        @php $cats = App\Models\Category::where('organization_id', auth()->user()->organization_id)->whereNull('parent_id')->get(); @endphp
                        @foreach ($cats as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ldr-field">
                    <label class="ldr-field__label">{{ __('ui.booking.purpose') }}</label>
                    <textarea class="ldr-textarea" name="purpose" id="purpose" rows="3" placeholder="{{ __('ui.booking.purpose_placeholder') }}" required></textarea>
                </div>

                <div class="ldr-field">
                    <label class="ldr-field__label">{{ __('ui.booking.period') }}</label>
                    <div class="ldr-date-range">
                        <input type="date" name="requested_start_date" id="requested_start_date" class="ldr-input" data-date-start required value="{{ old('requested_start_date') }}">
                        <span class="ldr-date-range__sep">—</span>
                        <input type="date" name="requested_end_date" id="requested_end_date" class="ldr-input" data-date-end required value="{{ old('requested_end_date') }}">
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-field__label">{{ __('ui.booking.duration') }}</label>
                    <span class="ldr-muted" data-duration-display>—</span>
                </div>

                @if ($conflict)
                    <div class="ldr-conflict-banner">
                        <x-icon name="alert-triangle" :size="18" class="ldr-conflict-banner__icon" />
                        <div>
                            <p class="ldr-conflict-banner__title">{{ __('ui.booking.conflict_title') }}</p>
                            <p class="ldr-conflict-banner__message">{{ $conflict['asset_name'] }} — {{ $conflict['start'] }} s/d {{ $conflict['end'] }}</p>
                            <p class="ldr-conflict-banner__message">{{ __('ui.booking.conflict_code', ['code' => $conflict['booking_code']]) }}</p>
                        </div>
                        <button type="button" class="ldr-btn ldr-btn--sm ldr-btn--primary" data-apply-range>
                            {{ __('ui.common.apply') }}
                        </button>
                    </div>
                @endif

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">
                        {{ __('ui.booking.submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection