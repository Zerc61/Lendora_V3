@extends('layouts.admin')

@section('title', 'Kiosk Scanner')

@section('content')
<div class="ldr-kiosk ldr-kiosk--embedded">
    <div class="ldr-kiosk__head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.kiosk.terminal') }}</span>
            <h1>{{ __('ui.kiosk.scanner_title') }}</h1>
            <p class="ldr-muted">{{ __('ui.kiosk.scanner_subtitle') }}</p>
        </div>

        <div class="ldr-kiosk__status">
            <span class="ldr-op-live">
                <span class="ldr-op-live__dot" aria-hidden="true"></span>
                {{ __('ui.kiosk.ready') }}
            </span>
            <span class="ldr-kiosk__shift">
                {{ $shift['label'] }} · {{ $shift['start'] }}–{{ $shift['end'] }} WIB
            </span>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.kiosk.queue') }}">
                <x-icon name="users" :size="16" />
                {{ __('ui.kiosk.queue_link') }}
                @if ($queue['waiting_count'] > 0)
                    <span class="ldr-badge ldr-badge--warning">{{ $queue['waiting_count'] }}</span>
                @endif
            </a>
        </div>
    </div>

    <div class="ldr-kiosk__stats">
        <div class="ldr-kiosk__stat">
            <span class="ldr-kiosk__stat-label">{{ __('ui.kiosk.stat_waiting') }}</span>
            <strong>{{ $queue['waiting_count'] }}</strong>
            <span class="ldr-muted">{{ __('ui.kiosk.stat_waiting_hint') }}</span>
        </div>
        <div class="ldr-kiosk__stat">
            <span class="ldr-kiosk__stat-label">{{ __('ui.kiosk.stat_done') }}</span>
            <strong>{{ $queue['done_count'] }}</strong>
            <span class="ldr-muted">{{ __('ui.kiosk.stat_done_hint') }}</span>
        </div>
        <div class="ldr-kiosk__stat">
            <span class="ldr-kiosk__stat-label">{{ __('ui.kiosk.stat_counters') }}</span>
            <strong>{{ $queue['counters']->count() }}</strong>
            <span class="ldr-muted">{{ __('ui.kiosk.stat_counters_hint') }}</span>
        </div>
    </div>

    @if (session('kiosk.receipt'))
        <div class="ldr-alert ldr-alert--success" role="status">
            {{ __('ui.kiosk.receipt_saved') }}
            <a href="{{ route('admin.handovers.index') }}">{{ __('ui.kiosk.receipt_link') }}</a>
        </div>
    @endif

    <div class="ldr-kiosk__grid">
        <section class="ldr-card ldr-scanner">
            <div class="ldr-card-head ldr-card-head--flush">
                <div>
                    <h2>{{ __('ui.kiosk.scan_title') }}</h2>
                    <p>{{ __('ui.kiosk.scan_hint') }}</p>
                </div>
            </div>

            <div class="ldr-scanner__viewport" aria-hidden="true">
                <span class="ldr-scanner__frame"></span>
                <x-icon name="aperture" :size="34" />
                <span class="ldr-scanner__line"></span>
                <span class="ldr-scanner__caption">{{ __('ui.kiosk.scan_caption') }}</span>
            </div>

            <form method="POST" action="{{ route('admin.kiosk.lookup') }}" class="ldr-scanner__form">
                @csrf
                <label class="ldr-sr-only" for="kiosk-query">{{ __('ui.kiosk.scan_label') }}</label>
                <div class="ldr-scanner__input">
                    <x-icon name="search" :size="18" />
                    <input id="kiosk-query"
                           class="ldr-input"
                           type="text"
                           name="query"
                           value="{{ old('query') }}"
                           placeholder="{{ __('ui.kiosk.scan_placeholder') }}"
                           maxlength="100"
                           autofocus
                           autocomplete="off"
                           required
                           data-kiosk-scan>
                    <button class="ldr-btn ldr-btn--primary" type="submit">
                        {{ __('ui.kiosk.scan_submit') }}
                        <x-icon name="arrow-right" :size="16" />
                    </button>
                </div>
                @error('query')
                    <p class="ldr-field-error">{{ $message }}</p>
                @enderror
            </form>

            <div class="ldr-kiosk__shortcuts">
                <span><kbd>Enter</kbd> {{ __('ui.kiosk.shortcut_scan') }}</span>
                <span><kbd>F1</kbd> {{ __('ui.kiosk.shortcut_focus') }}</span>
                <span><kbd>Esc</kbd> {{ __('ui.kiosk.shortcut_reset') }}</span>
            </div>

            <div class="ldr-scanner__queue">
                <div class="ldr-scanner__queue-head">
                    <h3>{{ __('ui.kiosk.next_queue') }}</h3>
                    <a class="ldr-link-sm" href="{{ route('admin.kiosk.queue') }}">{{ __('ui.kiosk.queue_link') }}</a>
                </div>

                @if ($queue['waiting']->isEmpty())
                    <p class="ldr-muted">{{ __('ui.kiosk.queue_empty') }}</p>
                @else
                    <ol class="ldr-scanner__queue-list">
                        @foreach ($queue['waiting']->take(4) as $ticket)
                            <li>
                                <strong>{{ $ticket->number }}</strong>
                                <span class="ldr-muted">{{ $ticket->service_type->label() }} · {{ $ticket->nim ?: '—' }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        <section class="ldr-card ldr-kiosk__result">
            @if ($lookup === null)
                <div class="ldr-kiosk__empty">
                    <x-icon name="scan-line" :size="40" />
                    <h2>{{ __('ui.kiosk.empty_title') }}</h2>
                    <p class="ldr-muted">{{ __('ui.kiosk.empty_description') }}</p>
                </div>
            @else
                @php
                    $booking = $lookup['booking'];
                    $preflight = $lookup['preflight'];
                    $isReturn = $lookup['type'] === \App\Enums\HandoverType::Return;
                @endphp

                <div class="ldr-kiosk__result-head">
                    <div>
                        <span class="ldr-mono ldr-kiosk__code">{{ $booking->code }}</span>
                        <h2>{{ $booking->borrower?->name ?? '—' }}</h2>
                        <span class="ldr-muted">
                            {{ $booking->borrower?->nim ? 'NIM '.$booking->borrower->nim : $booking->borrower?->email }}
                        </span>
                    </div>
                    <span class="ldr-kiosk__mode {{ $isReturn ? 'is-return' : 'is-pickup' }}">
                        <x-icon :name="$isReturn ? 'arrow-left-right' : 'package'" :size="16" />
                        {{ $lookup['type']->label() }}
                    </span>
                </div>

                <dl class="ldr-kiosk__facts">
                    <div>
                        <dt>{{ __('ui.booking.period') }}</dt>
                        <dd>
                            {{ $booking->requested_start_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                            –
                            {{ $booking->requested_end_date?->copy()->locale('id')->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.kiosk.booking_status') }}</dt>
                        <dd>{{ $booking->status->value }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}</dt>
                        <dd>{{ $booking->purpose }}</dd>
                    </div>
                </dl>

                @unless ($preflight['allowed'])
                    <p class="ldr-alert ldr-alert--danger" role="alert">
                        <x-icon name="alert-circle" :size="18" />
                        {{ $preflight['reason'] }}
                    </p>
                @endunless

                <form method="POST" action="{{ route('admin.kiosk.process') }}" class="ldr-kiosk__form" data-kiosk-process>
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="type" value="{{ $lookup['type']->value }}">
                    <input type="hidden" name="shift_label" value="{{ $shift['label'] }}">
                    <input type="hidden" name="borrower_signature" data-signature-input="borrower_signature">
                    <input type="hidden" name="operator_signature" data-signature-input="operator_signature">

                    <fieldset class="ldr-kiosk__units">
                        <legend>{{ __('ui.kiosk.units_legend') }}</legend>

                        @foreach ($booking->items as $item)
                            <div class="ldr-kiosk__unit">
                                <div class="ldr-kiosk__unit-head">
                                    <span class="ldr-kiosk__unit-icon"><x-icon name="camera" :size="18" /></span>
                                    <div>
                                        <strong>{{ $item->asset?->name ?? '—' }}</strong>
                                        <span class="ldr-mono">{{ $item->asset?->code }}</span>
                                    </div>
                                    <span class="ldr-badge ldr-badge--info">{{ __('ui.kiosk.barcode_ok') }}</span>
                                </div>

                                <div class="ldr-kiosk__unit-fields">
                                    <label class="ldr-field">
                                        <span>{{ __('ui.kiosk.condition') }}</span>
                                        <select class="ldr-input" name="conditions[{{ $item->id }}]" required>
                                            @foreach (\App\Enums\AssetCondition::cases() as $condition)
                                                <option value="{{ $condition->value }}" @selected($condition === \App\Enums\AssetCondition::Good)>
                                                    {{ __('ui.condition.'.$condition->value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label class="ldr-field">
                                        <span>{{ __('ui.kiosk.item_note') }}</span>
                                        <input class="ldr-input" type="text" name="item_notes[{{ $item->id }}]" maxlength="255" placeholder="{{ __('ui.kiosk.item_note_placeholder') }}">
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </fieldset>

                    <fieldset class="ldr-kiosk__checklist">
                        <legend>{{ __('ui.kiosk.checklist_legend') }}</legend>
                        @foreach (['sensor_clean' => __('ui.kiosk.check_sensor'), 'battery_full' => __('ui.kiosk.check_battery'), 'accessories_complete' => __('ui.kiosk.check_accessories')] as $key => $label)
                            <label class="ldr-checkbox">
                                <input type="checkbox" name="checklist[{{ $key }}]" value="1" checked>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="ldr-kiosk__form-grid">
                        <label class="ldr-field">
                            <span>{{ __('ui.kiosk.photos_label') }}</span>
                            <input class="ldr-input" type="file" name="photos[]" accept="image/*" multiple>
                        </label>

                        <label class="ldr-field">
                            <span>{{ __('ui.kiosk.notes_label') }}</span>
                            <textarea class="ldr-input" name="notes" rows="2" maxlength="1000" placeholder="{{ __('ui.kiosk.notes_placeholder') }}"></textarea>
                        </label>
                    </div>

                    @if ($isReturn)
                        <label class="ldr-checkbox">
                            <input type="checkbox" name="inspect" value="1" checked>
                            <span>{{ __('ui.kiosk.inspect_after_return') }}</span>
                        </label>
                        <label class="ldr-field">
                            <span>{{ __('ui.kiosk.inspection_notes') }}</span>
                            <textarea class="ldr-input" name="inspection_notes" rows="2" maxlength="2000"></textarea>
                        </label>
                    @endif

                    <div class="ldr-kiosk__signatures">
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
                        </div>
                    </div>

                    <div class="ldr-kiosk__submit">
                        <button class="ldr-btn ldr-btn--primary ldr-btn--lg" type="submit" @disabled(! $preflight['allowed'])>
                            <x-icon name="check-circle" :size="18" />
                            {{ __('ui.kiosk.confirm') }} {{ $lookup['type']->label() }}
                        </button>
                        <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.kiosk.index') }}">
                            {{ __('ui.kiosk.cancel') }}
                        </a>
                    </div>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
