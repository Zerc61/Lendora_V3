@extends('layouts.admin')

@section('title', __('ui.practicum.title'))

@php
    $days = [
        1 => __('ui.practicum.day_1'),
        2 => __('ui.practicum.day_2'),
        3 => __('ui.practicum.day_3'),
        4 => __('ui.practicum.day_4'),
        5 => __('ui.practicum.day_5'),
        6 => __('ui.practicum.day_6'),
        7 => __('ui.practicum.day_7'),
    ];

    $formatTime = fn (string $time): string => substr($time, 0, 5);
    $conflictIds = $conflicts
        ->flatMap(fn (array $conflict): array => [$conflict['first']->id, $conflict['second']->id])
        ->unique()
        ->all();
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.practicum.eyebrow') }}</span>
            <h1>{{ __('ui.practicum.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.practicum.subtitle') }}</p>
            <span class="ldr-prodi-head__meta">
                <x-icon name="calendar" :size="14" />
                {{ $semester['label'] }}
            </span>
        </div>
    </div>

    @if ($conflicts->isNotEmpty())
        <div class="ldr-alert ldr-alert--danger" role="status">
            <x-icon name="alert-circle" :size="18" />
            <div>
                <strong>{{ __('ui.practicum.conflict_title') }}: {{ $conflicts->count() }}</strong>
                <ul class="ldr-practicum-conflicts">
                    @foreach ($conflicts as $conflict)
                        <li>
                            {{ __('ui.practicum.conflict_line', [
                                'room' => $conflict['room']->name,
                                'day' => $days[$conflict['day']],
                                'first' => $conflict['first']->course?->name ?? '—',
                                'first_time' => $formatTime($conflict['first']->start_time).'–'.$formatTime($conflict['first']->end_time),
                                'second' => $conflict['second']->course?->name ?? '—',
                                'second_time' => $formatTime($conflict['second']->start_time).'–'.$formatTime($conflict['second']->end_time),
                            ]) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.practicum.kpi_rooms') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="layers" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['rooms'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.practicum.kpi_rooms_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.practicum.kpi_hours') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="calendar" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['scheduled_hours'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.practicum.kpi_hours_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.practicum.kpi_util') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['percent'] }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.practicum.kpi_util_hint', ['hours' => $kpis['capacity']]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.practicum.kpi_conflicts') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['conflicts'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.practicum.kpi_conflicts_hint') }}</span>
        </div>
    </div>

    <h2 class="ldr-section-title">{{ __('ui.practicum.grid_title') }}</h2>
    <p class="ldr-muted ldr-section-hint">{{ __('ui.practicum.grid_hint') }}</p>

    @if ($rooms->isEmpty())
        <x-empty-state
            icon="calendar"
            :title="__('ui.practicum.empty_title')"
            :description="__('ui.practicum.empty_description')"
        />
    @else
        <div class="ldr-table-card ldr-practicum-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table ldr-practicum-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.practicum.col_room') }}</th>
                            @foreach ($days as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rooms as $room)
                            <tr>
                                <th scope="row">
                                    <span class="ldr-table__name">{{ $room->name }}</span>
                                    <span class="ldr-table__sub">
                                        {{ $room->code }}
                                        @if ($room->capacity)
                                            · {{ __('ui.practicum.room_capacity', ['count' => $room->capacity]) }}
                                        @endif
                                    </span>
                                </th>

                                @foreach ($days as $day => $label)
                                    <td>
                                        @php
                                            $slots = $room->schedules->where('day_of_week', $day);
                                        @endphp

                                        @forelse ($slots as $slot)
                                            <div class="ldr-practicum-slot {{ in_array($slot->id, $conflictIds, true) ? 'is-conflict' : '' }}">
                                                <span class="ldr-practicum-slot__time">
                                                    {{ $formatTime($slot->start_time) }}–{{ $formatTime($slot->end_time) }}
                                                </span>
                                                <span class="ldr-practicum-slot__course">
                                                    {{ $slot->course?->code }} · {{ $slot->course?->name }}
                                                </span>
                                                <span class="ldr-practicum-slot__meta">
                                                    {{ $slot->course?->lecturer_name ?: '—' }}
                                                </span>
                                            </div>
                                        @empty
                                            <span class="ldr-muted ldr-practicum-empty">{{ __('ui.practicum.room_empty') }}</span>
                                        @endforelse
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="ldr-practicum-legend">
            <span class="ldr-badge ldr-badge--info">{{ __('ui.practicum.legend_normal') }}</span>
            <span class="ldr-badge ldr-badge--danger">{{ __('ui.practicum.legend_conflict') }}</span>
        </p>
    @endif
@endsection
