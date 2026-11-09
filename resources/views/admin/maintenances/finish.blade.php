@extends('layouts.admin')

@section('title', __('ui.maintenance.finish'))

@section('content')
    <div class="ldr-form-page ldr-form-narrow">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.maintenance.finish') }} · {{ $maintenance->asset?->code }}</h1>
                <p class="ldr-muted">{{ $maintenance->description }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.maintenances.finish.store', $maintenance) }}" class="ldr-card">
            @csrf

            <div class="ldr-field">
                <label class="ldr-label" for="result_notes">{{ __('ui.maintenance.result_notes') }}</label>
                <textarea id="result_notes" class="ldr-input" name="result_notes">{{ old('result_notes', $maintenance->result_notes) }}</textarea>
                @error('result_notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="cost">{{ __('ui.maintenance.cost') }}</label>
                <input id="cost" class="ldr-input" type="number" name="cost" min="0" value="{{ old('cost', $maintenance->cost) }}">
                @error('cost') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <label class="ldr-checkbox" for="condition_good">
                <input id="condition_good" type="checkbox" name="condition_good" value="1">
                {{ __('ui.maintenance.condition_good') }}
            </label>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.maintenance.save_result') }}</button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.maintenances.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
