@extends('layouts.borrower')

@section('title', __('ui.booking.new'))
@section('nav', 'bookings')

@section('content')
    @if ($assets->isEmpty())
        <a class="ldr-back-link" href="{{ route('borrower.catalog.index') }}">
            <x-icon name="chevron-left" :size="18" />
            {{ __('ui.catalog.back_to_catalog') }}
        </a>

        <x-empty-state
            icon="package"
            :title="__('ui.booking.empty_selection_title')"
            :description="__('ui.booking.empty_selection_description')"
        >
            <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.catalog.index') }}">
                {{ __('ui.nav.catalog') }}
            </a>
        </x-empty-state>
    @else
        <a class="ldr-back-link" href="{{ url()->previous() }}">
            <x-icon name="chevron-left" :size="18" />
            {{ __('ui.common.back') }}
        </a>

        <div class="ldr-page-title">
            <h1>{{ __('ui.booking.new') }}</h1>
            <p class="ldr-muted">{{ __('ui.booking.form_title') }}</p>
        </div>

        <x-conflict-banner :message="$errors->first('assets')" />

        <div class="ldr-booking-create__grid">
            <section class="ldr-card">
            <h2>{{ __('ui.booking.items_summary') }}</h2>

            <ul class="ldr-booking-items">
                @foreach ($assets as $asset)
                    <li class="ldr-booking-items__row">
                        <span class="ldr-booking-items__main">
                            <span class="ldr-booking-items__name">{{ $asset->name }}</span>
                            <span class="ldr-mono ldr-muted">{{ $asset->code }}</span>
                        </span>
                        <x-status-badge :status="$asset->status->value" />
                    </li>
                @endforeach
            </ul>

            <p class="ldr-form-hint">{{ __('ui.booking.items_readonly') }}</p>
        </section>

        <form method="POST" action="{{ route('borrower.bookings.store') }}" class="ldr-card ldr-booking-form" data-date-range>
            @csrf

            @foreach ($assets as $asset)
                <input type="hidden" name="assets[]" value="{{ $asset->id }}">
            @endforeach

            <div class="ldr-field">
                <label class="ldr-label" for="requested_start_date">
                    {{ __('ui.booking.start') }} <span class="ldr-label__required" aria-hidden="true">*</span>
                </label>
                <input id="requested_start_date"
                       class="ldr-input @error('requested_start_date') ldr-input--error @enderror"
                       type="date"
                       name="requested_start_date"
                       value="{{ old('requested_start_date') }}"
                       data-date-start
                       required>

                @error('requested_start_date')
                    <p class="ldr-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="requested_end_date">
                    {{ __('ui.booking.end') }} <span class="ldr-label__required" aria-hidden="true">*</span>
                </label>
                <input id="requested_end_date"
                       class="ldr-input @error('requested_end_date') ldr-input--error @enderror"
                       type="date"
                       name="requested_end_date"
                       value="{{ old('requested_end_date') }}"
                       data-date-end
                       required>

                @error('requested_end_date')
                    <p class="ldr-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="purpose">
                    {{ __('ui.booking.purpose') }} <span class="ldr-label__required" aria-hidden="true">*</span>
                </label>
                <textarea id="purpose"
                          class="ldr-input @error('purpose') ldr-input--error @enderror"
                          name="purpose"
                          maxlength="255"
                          placeholder="{{ __('ui.booking.purpose_placeholder') }}"
                          required>{{ old('purpose') }}</textarea>

                @error('purpose')
                    <p class="ldr-field__error">{{ $message }}</p>
                @enderror
            </div>

            <p class="ldr-form-hint">{{ __('ui.booking.max_days_hint', ['days' => $maxLoanDays]) }}</p>
            <p class="ldr-form-hint">{{ __('ui.booking.rules') }}</p>

            <div class="ldr-form-actions">
                <a class="ldr-btn ldr-btn--secondary" href="{{ url()->previous() }}">{{ __('ui.common.cancel') }}</a>
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.booking.submit') }}</button>
            </div>
        </form>
        </div>
    @endif
@endsection
