@extends('layouts.admin')

@section('title', __('ui.inspection.add'))

@php
    $resultHints = [
        'ok' => __('ui.inspection.result_ok_hint'),
        'needs_repair' => __('ui.inspection.result_repair_hint'),
        'damaged' => __('ui.inspection.result_damaged_hint'),
    ];
    $checklistItems = [
        'shutter_sensor' => ['label' => __('ui.inspection.check_shutter'), 'hint' => __('ui.inspection.check_shutter_hint')],
        'ports' => ['label' => __('ui.inspection.check_port'), 'hint' => __('ui.inspection.check_port_hint')],
        'lcd_evf' => ['label' => __('ui.inspection.check_lcd'), 'hint' => __('ui.inspection.check_lcd_hint')],
        'completeness' => ['label' => __('ui.inspection.check_completeness'), 'hint' => __('ui.inspection.check_completeness_hint')],
    ];
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.inspections.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.common.back') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W9 · {{ __('ui.nav.inspections') }}</span>
            <h1>{{ __('ui.inspection.add') }}</h1>
            <p class="ldr-muted">{{ __('ui.inspection.create_description') }}</p>
        </div>
    </div>

    <div class="ldr-handover-grid">
        <form method="POST"
              action="{{ route('admin.inspections.store') }}"
              class="ldr-handover-form"
              enctype="multipart/form-data">
            @csrf

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.inspection.unit_target') }}</h2>
                        <p>{{ __('ui.inspection.booking_item_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-hv-form-grid">
                    <div class="ldr-field">
                        <label class="ldr-label" for="asset_id">{{ __('ui.nav.assets') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <select id="asset_id" class="ldr-input" name="asset_id" required>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}" @selected((int) old('asset_id', $bookingItem?->asset_id) === $asset->id)>
                                    {{ $asset->code }} · {{ $asset->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('asset_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="context">{{ __('ui.inspection.context') }}</label>
                        <select id="context" class="ldr-input" name="context">
                            @foreach (\App\Enums\InspectionContext::cases() as $context)
                                <option value="{{ $context->value }}" @selected(old('context') === $context->value)>
                                    {{ __('ui.inspection_context.'.$context->value) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="ldr-form-hint">{{ __('ui.inspection.context_hint') }}</p>
                        @error('context') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="booking_item_id">{{ __('ui.inspection.booking_item') }}</label>
                        <input id="booking_item_id" class="ldr-input" type="number" name="booking_item_id" value="{{ old('booking_item_id', $bookingItem?->id) }}">
                        <p class="ldr-form-hint">{{ __('ui.inspection.booking_item_hint') }}</p>
                        @error('booking_item_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.inspection.result') }}</h2>
                        <p>{{ __('ui.inspection.result_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-segmented ldr-segmented--cards">
                    @foreach (\App\Enums\InspectionResult::cases() as $result)
                        <label class="ldr-segmented__item ldr-segmented__item--{{ $result->value }}">
                            <input type="radio"
                                   name="result"
                                   value="{{ $result->value }}"
                                   @checked(old('result', \App\Enums\InspectionResult::Ok->value) === $result->value)
                                   required>
                            <span>
                                <strong>{{ __('ui.inspection_result.'.$result->value) }}</strong>
                                <small>{{ $resultHints[$result->value] ?? '' }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('result') <p class="ldr-field__error">{{ $message }}</p> @enderror
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.inspection.checklist') }}</h2>
                        <p>{{ __('ui.inspection.checklist_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-checklist-grid">
                    @foreach ($checklistItems as $key => $item)
                        <label class="ldr-checklist-card">
                            <input type="checkbox" name="checklist[{{ $key }}]" value="1" @checked(old("checklist.$key", true))>
                            <span>
                                <strong>{{ $item['label'] }}</strong>
                                <small>{{ $item['hint'] }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="ldr-hv-form-grid">
                    <label class="ldr-field">
                        <span>{{ __('ui.inspection.score') }}</span>
                        <input class="ldr-input" type="number" name="condition_score" min="0" max="100" step="1" value="{{ old('condition_score') }}">
                        <span class="ldr-form-hint">{{ __('ui.inspection.score_hint') }}</span>
                        @error('condition_score') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>

                    <label class="ldr-field">
                        <span>{{ __('ui.inspection.photos') }}</span>
                        <input class="ldr-input" type="file" name="photos[]" accept="image/*" multiple>
                        @error('photos.*') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>
                </div>

                <label class="ldr-field">
                    <span>{{ __('ui.inspection.notes_title') }}</span>
                    <textarea class="ldr-input" name="notes" rows="4" maxlength="2000" placeholder="{{ __('ui.inspection.notes_title') }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </label>

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">
                        <x-icon name="check-circle" :size="18" />
                        {{ __('ui.inspection.save') }}
                    </button>
                    <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.inspections.index') }}">{{ __('ui.common.cancel') }}</a>
                </div>
            </section>
        </form>

        <aside class="ldr-handover-side">
            <section class="ldr-card ldr-hv-sop">
                <h2>{{ __('ui.inspection.sop_title') }}</h2>
                <ol>
                    <li>{{ __('ui.inspection.sop_1') }}</li>
                    <li>{{ __('ui.inspection.sop_2') }}</li>
                    <li>{{ __('ui.inspection.sop_3') }}</li>
                    <li>{{ __('ui.inspection.sop_4') }}</li>
                </ol>
            </section>

            <section class="ldr-card ldr-hv-sop">
                <h2>{{ __('ui.inspection.flow_title') }}</h2>
                <ol>
                    <li>{{ __('ui.inspection.flow_1') }}</li>
                    <li>{{ __('ui.inspection.flow_2') }}</li>
                    <li>{{ __('ui.inspection.flow_3') }}</li>
                </ol>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.inspection.officer_title') }}</h2>
                <div class="ldr-hv-ident__person">
                    <span class="ldr-hv-avatar" aria-hidden="true">
                        {{ mb_substr(auth()->user()?->name ?? '?', 0, 2) }}
                    </span>
                    <div>
                        <strong>{{ auth()->user()?->name }}</strong>
                        <span class="ldr-muted">{{ auth()->user()?->role?->name }}</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
@endsection
