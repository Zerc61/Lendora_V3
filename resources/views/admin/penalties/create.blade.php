@extends('layouts.admin')

@section('title', __('ui.penalty.create_title'))

@php
    $draftQty = max(1, (int) old('items.0.quantity', 1));
    $draftUnit = max(0, (int) old('items.0.unit_cost', 0));
    $draftSubtotal = $draftQty * $draftUnit;
    $draftSubsidy = (int) round($draftSubtotal * (int) old('subsidy_percent', 10) / 100);
    $draftAdmin = max(0, (int) old('admin_fee', 0));
    $draftTotal = max(0, $draftSubtotal - $draftSubsidy + $draftAdmin);
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.penalties.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.common.back') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">O3 · BAP &amp; Denda</span>
            <h1>{{ __('ui.penalty.create_title') }}</h1>
            <p class="ldr-muted">{{ __('ui.penalty.create_description') }}</p>
        </div>
    </div>

    <div class="ldr-handover-grid">
        <form method="POST" action="{{ route('admin.penalties.store') }}" class="ldr-handover-form">
            @csrf

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.registered') }}</h2>
                        <p>{{ __('ui.penalty.create_description') }}</p>
                    </div>
                </div>

                <div class="ldr-hv-form-grid">
                    <div class="ldr-field">
                        <label class="ldr-label" for="booking_id">{{ __('ui.booking.detail') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <select id="booking_id" class="ldr-input" name="booking_id" required>
                            <option value="">{{ __('ui.booking.filter_status') }}</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" @selected((int) old('booking_id', $selectedBookingId) === $booking->id)>
                                    {{ $booking->code }} · {{ $booking->borrower?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="borrower_id">{{ __('ui.penalty.borrower_data') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <select id="borrower_id" class="ldr-input" name="borrower_id" required>
                            <option value="">{{ __('ui.penalty.borrower_data') }}</option>
                            @foreach ($borrowers as $borrower)
                                <option value="{{ $borrower->id }}" @selected((int) old('borrower_id') === $borrower->id)>{{ $borrower->name }}</option>
                            @endforeach
                        </select>
                        @error('borrower_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="asset_id">{{ __('ui.nav.assets') }}</label>
                        <select id="asset_id" class="ldr-input" name="asset_id">
                            <option value="">—</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}" @selected((int) old('asset_id') === $asset->id)>{{ $asset->code }} · {{ $asset->name }}</option>
                            @endforeach
                        </select>
                        @error('asset_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="maintenance_id">{{ __('ui.inspection.linked_maintenance') }}</label>
                        <select id="maintenance_id" class="ldr-input" name="maintenance_id">
                            <option value="">—</option>
                            @foreach ($maintenances as $maintenance)
                                <option value="{{ $maintenance->id }}" @selected((int) old('maintenance_id') === $maintenance->id)>
                                    {{ $maintenance->code }} · {{ $maintenance->asset?->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('maintenance_id') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="incident_at">{{ __('ui.penalty.incident_date') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="incident_at" class="ldr-input" type="datetime-local" name="incident_at" value="{{ old('incident_at', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('incident_at') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="inspector_name">{{ __('ui.penalty.inspector') }} <span class="ldr-label__required" aria-hidden="true">*</span></label>
                        <input id="inspector_name" class="ldr-input" type="text" name="inspector_name" value="{{ old('inspector_name', auth()->user()->name) }}" required maxlength="150">
                        @error('inspector_name') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>

                    <div class="ldr-field">
                        <label class="ldr-label" for="case_category">{{ __('ui.penalty.case_category') }}</label>
                        <input id="case_category" class="ldr-input" type="text" name="case_category" value="{{ old('case_category') }}" maxlength="60">
                        @error('case_category') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="ldr-field">
                    <span>{{ __('ui.penalty.damage_desc') }} <span class="ldr-label__required" aria-hidden="true">*</span></span>
                    <input class="ldr-input" type="text" name="damage_description" value="{{ old('damage_description') }}" required maxlength="255">
                    @error('damage_description') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </label>

                <label class="ldr-field">
                    <span>{{ __('ui.penalty.chronology') }}</span>
                    <textarea class="ldr-input" name="chronology" rows="4" maxlength="2000">{{ old('chronology') }}</textarea>
                    @error('chronology') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </label>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.items_title') }}</h2>
                        <p>{{ __('ui.penalty.repair_subtotal') }}</p>
                    </div>
                </div>

                <div class="ldr-repeat-rows" data-dynamic-rows>
                    <div class="ldr-repeat-list" data-row-list>
                        <div class="ldr-repeat-row" data-row>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_description') }}</span>
                                <input class="ldr-input" type="text" name="items[0][description]" value="{{ old('items.0.description') }}" maxlength="200">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_part_code') }}</span>
                                <input class="ldr-input" type="text" name="items[0][part_code]" value="{{ old('items.0.part_code') }}" maxlength="60">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_quantity') }}</span>
                                <input class="ldr-input" type="number" name="items[0][quantity]" value="{{ old('items.0.quantity', 1) }}" min="1" max="999">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_unit_cost') }}</span>
                                <input class="ldr-input" type="number" name="items[0][unit_cost]" value="{{ old('items.0.unit_cost', 0) }}" min="0">
                            </label>
                            <button class="ldr-repeat-remove" type="button" data-remove-row aria-label="{{ __('ui.common.delete') }}">
                                <x-icon name="x" :size="16" />
                            </button>
                        </div>
                    </div>

                    <template data-row-template>
                        <div class="ldr-repeat-row" data-row>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_description') }}</span>
                                <input class="ldr-input" type="text" name="items[__INDEX__][description]" maxlength="200">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_part_code') }}</span>
                                <input class="ldr-input" type="text" name="items[__INDEX__][part_code]" maxlength="60">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_quantity') }}</span>
                                <input class="ldr-input" type="number" name="items[__INDEX__][quantity]" value="1" min="1" max="999">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_unit_cost') }}</span>
                                <input class="ldr-input" type="number" name="items[__INDEX__][unit_cost]" value="0" min="0">
                            </label>
                            <button class="ldr-repeat-remove" type="button" data-remove-row aria-label="{{ __('ui.common.delete') }}">
                                <x-icon name="x" :size="16" />
                            </button>
                        </div>
                    </template>

                    <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="button" data-add-row>
                        <x-icon name="plus" :size="16" />
                        {{ __('ui.penalty.add_item') }}
                    </button>
                </div>

                @error('items') <p class="ldr-field__error">{{ $message }}</p> @enderror
                @error('items.*.description') <p class="ldr-field__error">{{ $message }}</p> @enderror

                <div class="ldr-hv-form-grid">
                    <label class="ldr-field">
                        <span>{{ __('ui.penalty.subsidy_percent') }}</span>
                        <input class="ldr-input" type="number" name="subsidy_percent" value="{{ old('subsidy_percent', 10) }}" min="0" max="100">
                        @error('subsidy_percent') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>
                    <label class="ldr-field">
                        <span>{{ __('ui.penalty.admin_fee') }}</span>
                        <input class="ldr-input" type="number" name="admin_fee" value="{{ old('admin_fee', 0) }}" min="0">
                        @error('admin_fee') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>
                    <label class="ldr-field">
                        <span>{{ __('ui.penalty.payment_terms') }}</span>
                        <input class="ldr-input" type="number" name="payment_terms_days" value="{{ old('payment_terms_days', 7) }}" min="1" max="60" required>
                        @error('payment_terms_days') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>
                </div>

                <label class="ldr-field">
                    <span>{{ __('ui.penalty.photos_title') }}</span>
                    <input class="ldr-input" type="file" name="photos[]" accept="image/*" multiple>
                    @error('photos.*') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </label>

                <label class="ldr-field">
                    <span>{{ __('ui.penalty.damage') }} / {{ __('ui.booking.notes') }}</span>
                    <textarea class="ldr-input" name="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
                    @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </label>

                <div class="ldr-form-actions">
                    <button class="ldr-btn ldr-btn--primary" type="submit">
                        <x-icon name="check-circle" :size="18" />
                        {{ __('ui.common.save') }}
                    </button>
                    <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.penalties.index') }}">{{ __('ui.common.cancel') }}</a>
                </div>
            </section>
        </form>

        <aside class="ldr-handover-side">
            <section class="ldr-card">
                <h2>{{ __('ui.penalty.summary_title') }}</h2>
                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.penalty.repair_subtotal') }}</dt>
                        <dd>Rp {{ number_format($draftSubtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.subsidy') }}</dt>
                        <dd>−Rp {{ number_format($draftSubsidy, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.admin_fee') }}</dt>
                        <dd>Rp {{ number_format($draftAdmin, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.total') }}</dt>
                        <dd>Rp {{ number_format($draftTotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>
                <p class="ldr-muted">{{ __('ui.penalty.print_hint') }}</p>
            </section>

            <section class="ldr-card ldr-hv-sop">
                <h2>{{ __('ui.penalty.issue_action') }}</h2>
                <ol>
                    <li>{{ __('ui.penalty.create_description') }}</li>
                    <li>{{ __('ui.penalty.issue_action') }}</li>
                    <li>{{ __('ui.penalty.verify_payment') }}</li>
                </ol>
            </section>
        </aside>
    </div>
@endsection
