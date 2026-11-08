@extends('layouts.admin')

@section('title', __('ui.category.new'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.category.new') }}</h1>
        </div>

        <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.categories.index') }}">
            <x-icon name="chevron-left" :size="18" />
            {{ __('ui.common.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="ldr-card ldr-form-section ldr-form-narrow">
        @csrf

        <div class="ldr-field">
            <label class="ldr-label" for="parent_id">{{ __('ui.category.parent') }}</label>
            <select id="parent_id"
                    class="ldr-input @error('parent_id') ldr-input--error @enderror"
                    name="parent_id">
                <option value="">{{ __('ui.category.without_parent') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" @selected(old('parent_id') == $category['id'])>
                        {{ str_repeat('— ', $category['depth']) }}{{ $category['name'] }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="name">
                {{ __('ui.category.fields.name') }} <span class="ldr-label__required">*</span>
            </label>
            <input id="name"
                   class="ldr-input @error('name') ldr-input--error @enderror"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required>
            @error('name')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="slug">
                {{ __('ui.category.fields.slug') }} <span class="ldr-muted">({{ __('ui.common.optional') }})</span>
            </label>
            <input id="slug"
                   class="ldr-input @error('slug') ldr-input--error @enderror"
                   type="text"
                   name="slug"
                   value="{{ old('slug') }}">
            @error('slug')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-field">
            <label class="ldr-label" for="description">{{ __('ui.category.fields.description') }}</label>
            <textarea id="description"
                      class="ldr-input @error('description') ldr-input--error @enderror"
                      name="description"
                      rows="3">{{ old('description') }}</textarea>
            @error('description')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ldr-form-actions">
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.categories.index') }}">{{ __('ui.common.cancel') }}</a>
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
        </div>
    </form>
@endsection
