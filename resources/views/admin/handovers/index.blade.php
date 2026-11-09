@extends('layouts.admin')

@section('title', __('ui.nav.handovers'))

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
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.booking.code') }}</th>
                            <th>{{ __('ui.booking.borrower') }}</th>
                            <th>{{ __('ui.booking.assets') }}</th>
                            <th>{{ __('ui.booking.period') }}</th>
                            <th>{{ __('ui.booking.filter_status') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            @php
                                $isPickup = $booking->status === App\Enums\BookingStatus::Approved;
                            @endphp
                            <tr>
                                <td class="ldr-table__code">
                                    <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                </td>
                                <td>{{ $booking->borrower->name }}</td>
                                <td class="ldr-table__num">{{ $booking->items->count() }}</td>
                                <td class="ldr-table__num">
                                    {{ $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y') }}
                                    –
                                    {{ $booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
                                </td>
                                <td><x-status-badge type="booking" :status="$booking->status->value" /></td>
                                <td>
                                    <a class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                       href="{{ route('admin.bookings.handover', ['booking' => $booking, 'type' => $isPickup ? 'pickup' : 'return']) }}">
                                        {{ $isPickup ? __('ui.handover.pickup_action') : __('ui.handover.return_action') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$bookings" />
        </div>
    @endif
@endsection
