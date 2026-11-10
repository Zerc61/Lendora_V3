@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

@php
    $canHandover = auth()->user()->role->slug === 'operator';
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.nav.handovers') }}</h1>
            <p class="ldr-muted">{{ __('ui.handover.index_description') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.handovers.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="handover-search">{{ __('ui.handover.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="handover-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.handover.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="handover-type">{{ __('ui.handover.filter_type') }}</label>
            <select id="handover-type" class="ldr-input" name="type">
                <option value="">{{ __('ui.common.all') }}</option>
                <option value="pickup" @selected(($filters['type'] ?? null) === 'pickup')>{{ __('ui.handover.pickup_action') }}</option>
                <option value="return" @selected(($filters['type'] ?? null) === 'return')>{{ __('ui.handover.return_action') }}</option>
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.handovers.index') }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($bookings->isEmpty())
        <x-empty-state
            icon="arrow-left-right"
            :title="__('ui.handover.empty_title')"
            :description="__('ui.handover.empty_description')"
        />
    @else
        <div class="ldr-handover-list">
            @foreach ($bookings as $booking)
                @php
                    $isPickup = $booking->status === App\Enums\BookingStatus::Approved;
                @endphp

                <article class="ldr-handover-card">
                    <div class="ldr-handover-card__head">
                        <div>
                            <a class="ldr-mono ldr-handover-card__code" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                            <strong>{{ $booking->borrower->name }}</strong>
                        </div>

                        <span class="ldr-badge {{ $isPickup ? 'ldr-badge--info' : 'ldr-badge--warning' }}">
                            {{ $isPickup ? __('ui.handover.pickup_action') : __('ui.handover.return_action') }}
                        </span>
                    </div>

                    <div class="ldr-handover-card__facts">
                        <span>
                            <x-icon name="calendar" :size="15" />
                            {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
                            –
                            {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
                        </span>
                        <span>
                            <x-icon name="package" :size="15" />
                            {{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}
                        </span>
                    </div>

                    <div class="ldr-handover-card__items">
                        @foreach ($booking->items as $item)
                            @php
                                $photo = $item->asset?->photos->first();
                            @endphp
                            <span class="ldr-handover-card__item">
                                <span class="ldr-queue-item__media">
                                    @if ($photo)
                                        <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                             alt="{{ $item->asset->name }}"
                                             loading="lazy"
                                             width="40"
                                             height="40">
                                    @else
                                        <x-icon name="camera" :size="16" />
                                    @endif
                                </span>
                                <span>
                                    <strong>{{ $item->asset?->name ?? '—' }}</strong>
                                    <span class="ldr-mono">{{ $item->asset?->code }}</span>
                                </span>
                            </span>
                        @endforeach
                    </div>

                    <div class="ldr-handover-card__actions">
                        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.show', $booking) }}">
                            {{ __('ui.booking.detail_action') }}
                        </a>
                        @if ($canHandover)
                            <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                               href="{{ route('admin.bookings.handover', ['booking' => $booking, 'type' => $isPickup ? 'pickup' : 'return']) }}">
                                <x-icon name="arrow-left-right" :size="16" />
                                {{ $isPickup ? __('ui.handover.pickup_action') : __('ui.handover.return_action') }}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <x-pagination :paginator="$bookings" />
    @endif
@endsection
