@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

@php
    $isPickup = $type === App\Enums\HandoverType::Pickup;
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.handovers.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.handover.back_to_list') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <h1>{{ $type->label() }} · <span class="ldr-mono">{{ $booking->code }}</span></h1>
            <p class="ldr-muted">
                {{ $booking->borrower?->name }} ·
                {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
                –
                {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
            </p>
        </div>
    </div>

    <div class="ldr-handover-grid">
        <form method="POST" action="{{ route('admin.bookings.handover.store', $booking) }}" class="ldr-handover-form">
            @csrf
            <input type="hidden" name="type" value="{{ $type->value }}">

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
                        <h2>{{ __('ui.booking.assets') }}</h2>
                        <p>{{ __('ui.handover.condition_hint') }}</p>
                    </div>
                    <span class="ldr-queue-card__count">{{ __('ui.booking.items_count', ['count' => $items->count()]) }}</span>
                </div>

                <div class="ldr-handover-items">
                    @foreach ($items as $item)
                        @php
                            $asset = $item->asset;
                            $photo = $asset?->photos->first();
                        @endphp
                        <article class="ldr-handover-item">
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
                                </span>
                            </div>

                            <div class="ldr-field ldr-handover-item__field">
                                <label class="ldr-label" for="condition-{{ $item->id }}">
                                    {{ __('ui.handover.condition') }}
                                </label>
                                <select id="condition-{{ $item->id }}"
                                        class="ldr-input"
                                        name="conditions[{{ $item->id }}]"
                                        required>
                                    @foreach (\App\Enums\AssetCondition::cases() as $condition)
                                        <option value="{{ $condition->value }}" @selected(old('conditions.'.$item->id) === $condition->value)>
                                            {{ __('ui.condition.'.$condition->value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('conditions.'.$item->id) <p class="ldr-field__error">{{ $message }}</p> @enderror
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-field">
                    <label class="ldr-label" for="notes">{{ __('ui.booking.notes') }}</label>
                    <textarea id="notes" class="ldr-input" name="notes" maxlength="255" placeholder="{{ __('ui.handover.notes_hint') }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

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
                <h2>{{ __('ui.handover.summary') }}</h2>

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
                            {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
                            –
                            {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
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
        </aside>
    </div>
@endsection
