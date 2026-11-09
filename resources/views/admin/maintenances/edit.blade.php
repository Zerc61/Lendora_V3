@extends('layouts.admin')

@section('title', __('ui.maintenance.edit'))

@section('content')
    <div class="ldr-form-page ldr-form-narrow">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.maintenance.edit') }} · {{ $maintenance->asset?->code }}</h1>
                <p class="ldr-muted">{{ $maintenance->description }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.maintenances.update', $maintenance) }}" class="ldr-card">
            @csrf
            @method('PUT')

            <div class="ldr-field">
                <label class="ldr-label" for="assigned_to">{{ __('ui.maintenance.technician') }}</label>
                <select id="assigned_to" class="ldr-input" name="assigned_to">
                    <option value="">{{ __('ui.maintenance.unassigned') }}</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected(old('assigned_to', $maintenance->assigned_to) == $technician->id)>{{ $technician->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.maintenances.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.maintenances.destroy', $maintenance) }}"
              onsubmit="return confirm(@js(__('ui.maintenance.cancel_confirm')));">
            @csrf
            @method('DELETE')
            <button type="submit" class="ldr-btn ldr-btn--danger">
                {{ __('ui.maintenance.cancel_task') }}
            </button>
        </form>
    </div>
@endsection
