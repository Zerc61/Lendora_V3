@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

@php
    $isPickup = $type === App\Enums\HandoverType::Pickup;
    $accessoryOptions = [
        'unit' => __('ui.handover.acc_unit'),
        'lens' => __('ui.handover.acc_lens'),
        'battery' => __('ui.handover.acc_battery'),
        'charger' => __('ui.handover.acc_charger'),
        'case' => __('ui.handover.acc_case'),
        'manual' => __('ui.handover.acc_manual'),
    ];
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.handovers.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.handover.back_to_list') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W8 · {{ __('ui.nav.handovers') }}</span>
            <h1>{{ $isPickup ? __('ui.handover.pickup_title') : __('ui.handover.return_title') }}</h1>
            <p class="ldr-muted">{{ $isPickup ? __('ui.handover.mode_pickup_hint') : __('ui.handover.mode_return_hint') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <span class="ldr-mono">{{ $booking->code }}</span>
            <x-status-badge type="booking" :status="$booking->status->value" />
        </div>
    </div>

    <div class="ldr-handover-grid">
        <form method="POST"
              action="{{ route('admin.bookings.handover.store', $booking) }}"
              class="ldr-handover-form"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="{{ $type->value }}">

            <section class="ldr-card ldr-hv-ident">
                <div class="ldr-hv-ident__person">
                    <span class="ldr-hv-avatar" aria-hidden="true">
                        {{ mb_substr($booking->borrower?->name ?? '?', 0, 2) }}
                    </span>
                    <div>
                        <strong>{{ $booking->borrower?->name ?? '—' }}</strong>
                        <span class="ldr-muted">
                            {{ $booking->borrower?->nim ? 'NIM '.$booking->borrower->nim : $booking->borrower?->email }}
                        </span>
                    </div>
                </div>

                <dl class="ldr-hv-ident__facts">
                    <div>
                        <dt>{{ __('ui.booking.code') }}</dt>
                        <dd class="ldr-mono">{{ $booking->code }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.period') }}</dt>
                        <dd>
                            {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                            –
                            {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.handover.units_count') }}</dt>
                        <dd>{{ __('ui.booking.items_count', ['count' => $items->count()]) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ldr-handover-banner {{ $isPickup ? 'is-pickup' : 'is-return' }}">
                <span class="ldr-handover-banner__icon">
                    <x-icon :name="$isPickup ? 'package' : 'arrow-left-right'" :size="20" />
                </span>
                <div>
                    <strong>{{ $isPickup ? __('ui.handover.pickup_title') : __('ui.handover.return_title') }}</strong>
                    <p>{{ $isPickup ? __('ui.handover.mode_pickup_hint') : __('ui.handover.mode_return_hint') }}</p>
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.handover.checklist_title') }}</h2>
                        <p>{{ __('ui.handover.condition_hint') }}</p>
                    </div>
                    <span class="ldr-queue-card__count">{{ __('ui.booking.items_count', ['count' => $items->count()]) }}</span>
                </div>

                <div class="ldr-handover-items">
                    @foreach ($items as $item)
                        @php
                            $asset = $item->asset;
                            $photo = $asset?->photos->first();
                            $selectedCondition = old('conditions.'.$item->id);
                        @endphp

                        <article class="ldr-handover-item ldr-hv-unit">
                            <div class="ldr-hv-unit__head">
                                <span class="ldr-unit__media">
                                    @if ($photo)
                                        <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                             alt="{{ $asset->name }}"
                                             loading="lazy"
                                             width="72"
                                             height="72">
                                    @else
                                        <x-icon name="camera" :size="24" />
                                    @endif
                                </span>

                                <div class="ldr-handover-item__body">
                                    <span class="ldr-mono">{{ $asset?->code }}</span>
                                    <strong>{{ $asset?->name ?? '—' }}</strong>
                                    <span class="ldr-muted">
                                        {{ collect([$asset?->brand, $asset?->model])->filter()->join(' · ') ?: '—' }}
                                        @if ($asset?->serial_number)
                                            · S/N {{ $asset->serial_number }}
                                        @endif
                                    </span>
                                </div>

                                <span class="ldr-badge ldr-badge--{{ $isPickup ? 'info' : 'warning' }}">
                                    {{ $isPickup ? __('ui.handover.out_condition') : __('ui.handover.in_condition') }}
                                </span>
                            </div>

                            <fieldset class="ldr-hv-condition">
                                <legend class="ldr-label">{{ __('ui.handover.condition') }}</legend>
                                <div class="ldr-segmented">
                                    @foreach (\App\Enums\AssetCondition::cases() as $condition)
                                        <label class="ldr-segmented__item">
                                            <input type="radio"
                                                   name="conditions[{{ $item->id }}]"
                                                   value="{{ $condition->value }}"
                                                   @checked($selectedCondition === $condition->value || ($selectedCondition === null && $condition === \App\Enums\AssetCondition::Good))
                                                   required>
                                            <span>{{ __('ui.condition.'.$condition->value) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('conditions.'.$item->id)
                                    <p class="ldr-field__error">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            <fieldset class="ldr-hv-accessories">
                                <legend class="ldr-label">{{ __('ui.handover.accessories_title') }}</legend>
                                <div class="ldr-hv-accessories__grid">
                                    @foreach ($accessoryOptions as $key => $label)
                                        <label class="ldr-checkbox">
                                            <input type="checkbox" name="accessories[{{ $item->id }}][]" value="{{ $key }}" checked>
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <label class="ldr-field">
                                <span>{{ __('ui.handover.item_note') }}</span>
                                <input class="ldr-input"
                                       type="text"
                                       name="item_notes[{{ $item->id }}]"
                                       value="{{ old('item_notes.'.$item->id) }}"
                                       maxlength="255"
                                       placeholder="{{ __('ui.handover.item_note_placeholder') }}">
                            </label>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.handover.notes_title') }}</h2>
                        <p>{{ __('ui.handover.notes_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-hv-form-grid">
                    <label class="ldr-field">
                        <span>{{ __('ui.handover.photos_label') }}</span>
                        <input class="ldr-input" type="file" name="photos[]" accept="image/*" multiple>
                        @error('photos.*') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>

                    <label class="ldr-field">
                        <span>{{ __('ui.booking.notes') }}</span>
                        <textarea class="ldr-input" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                        @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
                    </label>
                </div>

                <div class="ldr-hv-signatures">
                    <div class="ldr-signature">
                        <div class="ldr-signature__head">
                            <span>{{ __('ui.kiosk.sign_borrower') }}</span>
                            <button type="button" class="ldr-link-sm" data-signature-clear="borrower_signature">
                                {{ __('ui.kiosk.sign_clear') }}
                            </button>
                        </div>
                        <canvas class="ldr-signature__pad"
                                data-signature="borrower_signature"
                                width="320"
                                height="120"
                                aria-label="{{ __('ui.kiosk.sign_borrower') }}"></canvas>
                        <span class="ldr-muted">{{ $booking->borrower?->name ?? '—' }}</span>
                        <input type="hidden" name="borrower_signature" data-signature-input="borrower_signature">
                    </div>

                    <div class="ldr-signature">
                        <div class="ldr-signature__head">
                            <span>{{ __('ui.kiosk.sign_operator') }}</span>
                            <button type="button" class="ldr-link-sm" data-signature-clear="operator_signature">
                                {{ __('ui.kiosk.sign_clear') }}
                            </button>
                        </div>
                        <canvas class="ldr-signature__pad"
                                data-signature="operator_signature"
                                width="320"
                                height="120"
                                aria-label="{{ __('ui.kiosk.sign_operator') }}"></canvas>
                        <span class="ldr-muted">{{ auth()->user()?->name }}</span>
                        <input type="hidden" name="operator_signature" data-signature-input="operator_signature">
                    </div>
                </div>

                <label class="ldr-hv-statement">
                    <input type="checkbox" name="agreement" value="1" required>
                    <span>{{ __('ui.handover.statement') }}</span>
                </label>

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">
                        <x-icon name="check-circle" :size="18" />
                        {{ $isPickup ? __('ui.handover.submit_pickup') : __('ui.handover.submit_return') }}
                    </button>
                    <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.handovers.index') }}">{{ __('ui.common.cancel') }}</a>
                </div>
            </section>
        </form>

        <aside class="ldr-handover-side">
            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <h2>{{ __('ui.handover.summary') }}</h2>
                </div>

                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.booking.code') }}</dt>
                        <dd class="ldr-mono">{{ $booking->code }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.borrower') }}</dt>
                        <dd>{{ $booking->borrower?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.period') }}</dt>
                        <dd>
                            {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                            –
                            {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.filter_status') }}</dt>
                        <dd><x-status-badge type="booking" :status="$booking->status->value" /></dd>
                    </div>
                </dl>

                <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.bookings.show', $booking) }}">
                    {{ __('ui.booking.detail') }}
                </a>
            </section>

            <section class="ldr-card ldr-hv-sop">
                <h2>{{ __('ui.handover.sop_title') }}</h2>
                <ol>
                    <li>{{ __('ui.handover.sop_1') }}</li>
                    <li>{{ __('ui.handover.sop_2') }}</li>
                    <li>{{ __('ui.handover.sop_3') }}</li>
                    <li>{{ __('ui.handover.sop_4') }}</li>
                </ol>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.handover.officer_title') }}</h2>
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
