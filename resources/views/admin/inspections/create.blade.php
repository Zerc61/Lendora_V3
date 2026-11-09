@extends('layouts.admin')

@section('title', __('ui.inspection.add'))

@section('content')
    <div class="ldr-form-page ldr-form-narrow">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.inspection.add') }}</h1>
                <p class="ldr-muted">{{ __('ui.inspection.create_description') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.inspections.store') }}" class="ldr-card">
            @csrf

            <div class="ldr-field">
                <label class="ldr-label" for="asset_id">{{ __('ui.nav.assets') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                <select id="asset_id" class="ldr-input" name="asset_id" required>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected($bookingItem?->asset_id === $asset->id)>{{ $asset->code }} · {{ $asset->name }}</option>
                    @endforeach
                </select>
                @error('asset_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="booking_item_id">{{ __('ui.inspection.booking_item') }}</label>
                <input id="booking_item_id" class="ldr-input" type="number" name="booking_item_id" value="{{ $bookingItem?->id }}">
                <p class="ldr-form-hint">{{ __('ui.inspection.booking_item_hint') }}</p>
                @error('booking_item_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="result">{{ __('ui.inspection.result') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                <select id="result" class="ldr-input" name="result" required>
                    @foreach (\App\Enums\InspectionResult::cases() as $result)
                        <option value="{{ $result->value }}" @selected(old('result') === $result->value)>
                            {{ __('ui.inspection_result.'.$result->value) }}
                        </option>
                    @endforeach
                </select>
                @error('result') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="notes">{{ __('ui.booking.notes') }}</label>
                <textarea id="notes" class="ldr-input" name="notes">{{ old('notes') }}</textarea>
                @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.inspections.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
