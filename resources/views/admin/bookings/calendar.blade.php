@extends('layouts.admin')

@section('title', __('ui.booking.calendar'))

@php
    $daysInMonth = $month->daysInMonth;
    $leading = $month->copy()->startOfMonth()->dayOfWeekIso - 1;
    $totalCells = (int) ceil(($leading + $daysInMonth) / 7) * 7;
    $today = now()->toDateString();

    $previousMonth = $month->copy()->subMonthNoOverflow()->format('Y-m');
    $nextMonth = $month->copy()->addMonthNoOverflow()->format('Y-m');
    $categoryQuery = $filters['category_id'] ? ['category_id' => $filters['category_id']] : [];

    $weekdays = __('ui.booking.weekdays');
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.booking.calendar') }}</h1>
            <p class="ldr-muted">{{ $month->copy()->locale('id')->translatedFormat('F Y') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.index') }}">
                {{ __('ui.booking.back_to_queue') }}
            </a>
        </div>
    </div>

    <div class="ldr-calendar-toolbar">
        <div class="ldr-calendar-nav">
            <a class="ldr-icon-btn"
               href="{{ route('admin.bookings.calendar', array_filter([...$categoryQuery, 'month' => $previousMonth])) }}"
               aria-label="{{ __('ui.booking.calendar_prev') }}">
                <x-icon name="chevron-left" :size="18" />
            </a>

            <span class="ldr-calendar-nav__title">{{ $month->copy()->locale('id')->translatedFormat('F Y') }}</span>

            <a class="ldr-icon-btn"
               href="{{ route('admin.bookings.calendar', array_filter([...$categoryQuery, 'month' => $nextMonth])) }}"
               aria-label="{{ __('ui.booking.calendar_next') }}">
                <x-icon name="chevron-right" :size="18" />
            </a>
        </div>

        <form method="GET" action="{{ route('admin.bookings.calendar') }}" class="ldr-filter-bar ldr-filter-bar--compact" data-auto-submit>
            <div class="ldr-filter-bar__select">
                <label class="ldr-sr-only" for="calendar-month">{{ __('ui.booking.month') }}</label>
                <input id="calendar-month"
                       class="ldr-input"
                       type="month"
                       name="month"
                       value="{{ $filters['month'] }}"
                       data-auto-submit-field>
            </div>

            <div class="ldr-filter-bar__select">
                <label class="ldr-sr-only" for="calendar-category">{{ __('ui.asset.fields.category') }}</label>
                <select id="calendar-category" class="ldr-input" name="category_id" data-auto-submit-field>
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
        </form>
    </div>

    <div class="ldr-calendar-legend" aria-label="{{ __('ui.booking.calendar_legend') }}">
        <span><i class="is-free"></i> {{ __('ui.booking.calendar_legend_free') }}</span>
        <span><i class="is-approved"></i> {{ __('ui.booking.calendar_legend_booked') }}</span>
        <span><i class="is-active"></i> {{ __('ui.booking.calendar_legend_active') }}</span>
    </div>

    @if ($units->isEmpty())
        <x-empty-state
            icon="calendar"
            :title="__('ui.asset.empty_title')"
            :description="__('ui.booking.calendar_empty')"
        />
    @else
        <div class="ldr-calendar-card">
            <div class="ldr-calendar" role="grid" aria-label="{{ $month->copy()->locale('id')->translatedFormat('F Y') }}">
                @foreach ($weekdays as $weekday)
                    <div class="ldr-calendar__weekday" role="columnheader">{{ $weekday }}</div>
                @endforeach

                @for ($cell = 0; $cell < $totalCells; $cell++)
                    @php
                        $dayNumber = $cell - $leading + 1;
                        $inMonth = $dayNumber >= 1 && $dayNumber <= $daysInMonth;
                        $date = $inMonth ? $month->copy()->day($dayNumber) : null;
                        $dateString = $date?->toDateString();
                        $isToday = $dateString === $today;
                    @endphp

                    <div class="ldr-calendar__cell {{ $inMonth ? '' : 'is-outside' }} {{ $isToday ? 'is-today' : '' }}"
                         role="gridcell">
                        @if ($inMonth)
                            <span class="ldr-calendar__date">{{ $dayNumber }}</span>

                            <div class="ldr-calendar__chips">
                                @foreach ($units as $unit)
                                    @foreach ($ranges[$unit->id] ?? [] as $range)
                                        @if ($dateString >= $range['start'] && $dateString <= $range['end'])
                                            <a class="ldr-calendar__chip ldr-calendar__chip--{{ $range['status'] }}"
                                               href="{{ route('admin.bookings.index', ['status' => 'all', 'search' => $range['code']]) }}"
                                               title="{{ $unit->code }} · {{ $range['code'] }}">
                                                {{ $unit->code }} · {{ $range['code'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    @endif
@endsection
