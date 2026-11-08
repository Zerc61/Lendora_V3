@extends('layouts.admin')

@section('title', __('ui.asset.edit'))

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.asset.edit') }}</h1>
            <p class="ldr-muted ldr-mono">{{ $asset->code }}</p>
        </div>

        <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.assets.index') }}">
            <x-icon name="chevron-left" :size="18" />
            {{ __('ui.common.back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.assets.update', $asset) }}" enctype="multipart/form-data" class="ldr-form-page">
        @csrf
        @method('PUT')

        <div class="ldr-form-grid">
            <section class="ldr-card ldr-form-section">
                <h2>{{ __('ui.asset.identity') }}</h2>

                <div class="ldr-field">
                    <span class="ldr-label">{{ __('ui.asset.fields.code') }}</span>
                    <p class="ldr-readonly-code">{{ $asset->code }}</p>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="name">
                        {{ __('ui.asset.fields.name') }} <span class="ldr-label__required">*</span>
                    </label>
                    <input id="name"
                           class="ldr-input @error('name') ldr-input--error @enderror"
                           type="text"
                           name="name"
                           value="{{ old('name', $asset->name) }}"
                           required>
                    @error('name')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="category_id">
                        {{ __('ui.asset.fields.category') }} <span class="ldr-label__required">*</span>
                    </label>
                    <select id="category_id"
                            class="ldr-input @error('category_id') ldr-input--error @enderror"
                            name="category_id"
                            required>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}" @selected(old('category_id', $asset->category_id) == $category['id'])>
                                {{ str_repeat('— ', $category['depth']) }}{{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ldr-field-row">
                    <div class="ldr-field">
                        <label class="ldr-label" for="brand">{{ __('ui.asset.fields.brand') }}</label>
                        <input id="brand"
                               class="ldr-input @error('brand') ldr-input--error @enderror"
                               type="text"
                               name="brand"
                               value="{{ old('brand', $asset->brand) }}">
                        @error('brand')
                            <p class="ldr-field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="model">{{ __('ui.asset.fields.model') }}</label>
                        <input id="model"
                               class="ldr-input @error('model') ldr-input--error @enderror"
                               type="text"
                               name="model"
                               value="{{ old('model', $asset->model) }}">
                        @error('model')
                            <p class="ldr-field__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="serial_number">{{ __('ui.asset.fields.serial_number') }}</label>
                    <input id="serial_number"
                           class="ldr-input @error('serial_number') ldr-input--error @enderror"
                           type="text"
                           name="serial_number"
                           value="{{ old('serial_number', $asset->serial_number) }}">
                    @error('serial_number')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="description">{{ __('ui.asset.fields.description') }}</label>
                    <textarea id="description"
                              class="ldr-input @error('description') ldr-input--error @enderror"
                              name="description"
                              rows="4">{{ old('description', $asset->description) }}</textarea>
                    @error('description')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="ldr-card ldr-form-section">
                <h2>{{ __('ui.asset.operational') }}</h2>

                <div class="ldr-field">
                    <span class="ldr-label">{{ __('ui.asset.fields.status') }}</span>
                    <div class="ldr-readonly-field">
                        <x-status-badge :status="$asset->status->value" />
                        <span class="ldr-muted">{{ __('ui.asset.status_hint') }}</span>
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="condition_status">{{ __('ui.asset.fields.condition_status') }}</label>
                    <select id="condition_status"
                            class="ldr-input @error('condition_status') ldr-input--error @enderror"
                            name="condition_status">
                        @foreach (App\Enums\AssetCondition::cases() as $condition)
                            <option value="{{ $condition->value }}" @selected(old('condition_status', $asset->condition_status->value) === $condition->value)>
                                {{ __('ui.condition.'.$condition->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('condition_status')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ldr-field-row">
                    <div class="ldr-field">
                        <label class="ldr-label" for="purchase_date">{{ __('ui.asset.fields.purchase_date') }}</label>
                        <input id="purchase_date"
                               class="ldr-input @error('purchase_date') ldr-input--error @enderror"
                               type="date"
                               name="purchase_date"
                               value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}">
                        @error('purchase_date')
                            <p class="ldr-field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="purchase_price">{{ __('ui.asset.fields.purchase_price') }}</label>
                        <input id="purchase_price"
                               class="ldr-input @error('purchase_price') ldr-input--error @enderror"
                               type="number"
                               min="0"
                               step="1"
                               name="purchase_price"
                               value="{{ old('purchase_price', $asset->purchase_price) }}">
                        @error('purchase_price')
                            <p class="ldr-field__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="location">{{ __('ui.asset.fields.location') }}</label>
                    <input id="location"
                           class="ldr-input @error('location') ldr-input--error @enderror"
                           type="text"
                           name="location"
                           value="{{ old('location', $asset->location) }}">
                    @error('location')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="notes">{{ __('ui.asset.fields.notes') }}</label>
                    <textarea id="notes"
                              class="ldr-input @error('notes') ldr-input--error @enderror"
                              name="notes"
                              rows="3">{{ old('notes', $asset->notes) }}</textarea>
                    @error('notes')
                        <p class="ldr-field__error">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </div>

        <section class="ldr-card ldr-form-section">
            <h2>{{ __('ui.asset.photos') }}</h2>

            @if ($asset->photos->isNotEmpty())
                <div class="ldr-uploader__grid ldr-uploader__grid--existing">
                    @foreach ($asset->photos as $photo)
                        <figure class="ldr-photo">
                            <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                 alt="{{ $asset->name }}"
                                 loading="lazy"
                                 width="160"
                                 height="120">

                            @if ($photo->is_primary)
                                <figcaption class="ldr-photo__primary">{{ __('ui.asset.photo_primary') }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="ldr-muted">{{ __('ui.asset.photo_empty') }}</p>
            @endif

            <div class="ldr-uploader" data-photo-uploader>
                <label class="ldr-uploader__drop" for="photos">
                    <x-icon name="camera" :size="24" />
                    <span>{{ __('ui.asset.photos') }}</span>
                    <small>{{ __('ui.asset.photo_hint') }}</small>
                    <input id="photos"
                           class="ldr-sr-only"
                           type="file"
                           name="photos[]"
                           accept="image/*"
                           multiple
                           data-photo-input>
                </label>

                <div class="ldr-uploader__grid" data-photo-preview></div>
            </div>

            @error('photos')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
            @error('photos.*')
                <p class="ldr-field__error">{{ $message }}</p>
            @enderror
        </section>

        <div class="ldr-form-actions">
            <button type="button"
                    class="ldr-btn ldr-btn--danger"
                    data-confirm-open="delete-asset-{{ $asset->id }}">
                {{ __('ui.common.delete') }}
            </button>

            <span class="ldr-form-actions__spacer"></span>

            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.assets.index') }}">{{ __('ui.common.cancel') }}</a>
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.common.save') }}</button>
        </div>
    </form>

    <x-confirm-modal
        :id="'delete-asset-'.$asset->id"
        :title="__('ui.asset.delete_title')"
        :message="__('ui.asset.delete_message', ['code' => $asset->code])"
        :action="route('admin.assets.destroy', $asset)"
        method="DELETE"
        variant="danger"
    />
@endsection
