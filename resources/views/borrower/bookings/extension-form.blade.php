@extends('layouts.borrower')

@section('title', __('ui.booking.extension'))
@section('nav', 'bookings')

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.booking.extension') }}</h1>
            <p class="ldr-muted">{{ __('ui.booking.extension_help') }}</p>
        </div>
        <a href="{{ route('borrower.bookings.show', $booking) }}" class="ldr-btn ldr-btn--ghost">
            {{ __('ui.common.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('borrower.bookings.extension.request', $booking) }}" class="ldr-extension-form">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="new_end_date">{{ __('ui.booking.new_end_date') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
            <div class="ldr-input-group">
                <x-icon name="calendar" :size="18" />
                <input id="new_end_date" name="new_end_date" type="date" class="ldr-input @error('new_end_date') ldr-input--error @enderror" required min="{{ ($booking->due_at ?? $booking->requested_end_date)->copy()->addDay()->format('Y-m-d') }}">
            </div>
            @error('new_end_date') <p class="ldr-field__error">{{ $message }}</p> @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="reason">{{ __('ui.booking.extension_reason') }}</label>
            <textarea id="reason" name="reason" class="ldr-textarea" rows="3" placeholder="{{ __('ui.booking.extension_reason_placeholder') }}">{{ old('reason') }}</textarea>
        </div>

        <div class="ldr-form-actions">
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.submit') }}</button>
        </div>
    </form>
@endsection