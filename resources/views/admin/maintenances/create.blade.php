@extends('layouts.admin')

@section('title', __('ui.maintenance.add'))

@section('content')
    <div class="ldr-form-page ldr-form-narrow">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.maintenance.add') }}</h1>
                <p class="ldr-muted">{{ __('ui.maintenance.create_description') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.maintenances.store') }}" class="ldr-card">
            @csrf

            <div class="ldr-field">
                <label class="ldr-label" for="asset_id">{{ __('ui.nav.assets') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                <select id="asset_id" class="ldr-input" name="asset_id" required>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>{{ $asset->code }} · {{ $asset->name }}</option>
                    @endforeach
                </select>
                @error('asset_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="assigned_to">{{ __('ui.maintenance.technician') }}</label>
                <select id="assigned_to" class="ldr-input" name="assigned_to">
                    <option value="">{{ __('ui.maintenance.unassigned') }}</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected(old('assigned_to') == $technician->id)>{{ $technician->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-label" for="description">{{ __('ui.maintenance.description') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                <textarea id="description" class="ldr-input" name="description" required>{{ old('description') }}</textarea>
                @error('description') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-hv-form-grid">
                <div class="ldr-field">
                    <label class="ldr-label" for="priority">{{ __('ui.maintenance.priority') }}</label>
                    <select id="priority" class="ldr-input" name="priority">
                        @foreach (\App\Enums\MaintenancePriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>
                                {{ __('ui.maintenance_priority.'.$priority->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="due_at">{{ __('ui.maintenance.due_at') }}</label>
                    <input id="due_at" class="ldr-input" type="date" name="due_at" value="{{ old('due_at') }}">
                    @error('due_at') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="vendor">{{ __('ui.maintenance.assignment') }}</label>
                    <input id="vendor" class="ldr-input" type="text" name="vendor" value="{{ old('vendor') }}" maxlength="120">
                    @error('vendor') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="ldr-form-actions">
                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
                <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.maintenances.index') }}">{{ __('ui.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
