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

    <section class="ldr-card ldr-practicum-plot">
        <h2 class="ldr-section-title">{{ __('ui.practicum.plot_title') }}</h2>
        <p class="ldr-muted ldr-section-hint">{{ __('ui.practicum.plot_hint') }}</p>

        <form method="POST" action="{{ route('admin.practicum.store') }}" class="ldr-practicum-plot__form">
            @csrf

            <div class="ldr-field">
                <label class="ldr-field__label" for="plot-course">{{ __('ui.course_quota.col_course') }}</label>
                <select class="ldr-input" name="course_id" id="plot-course" required>
                    <option value="">{{ __('ui.practicum.plot_choose') }}</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) old('course_id') === $course->id)>
                            {{ $course->code }} · {{ $course->name }}
                        </option>
                    @endforeach
                </select>
                @error('course_id') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-field__label" for="plot-room">{{ __('ui.practicum.col_room') }}</label>
                <select class="ldr-input" name="room_id" id="plot-room" required>
                    <option value="">{{ __('ui.practicum.plot_choose') }}</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected((int) old('room_id') === $room->id)>
                            {{ $room->code }} · {{ $room->name }}
                        </option>
                    @endforeach
                </select>
                @error('room_id') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-field__label" for="plot-day">{{ __('ui.practicum.plot_day') }}</label>
                <select class="ldr-input" name="day_of_week" id="plot-day" required>
                    @foreach ($days as $value => $label)
                        <option value="{{ $value }}" @selected((int) old('day_of_week') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('day_of_week') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-field__label" for="plot-start">{{ __('ui.practicum.plot_start') }}</label>
                <input class="ldr-input" type="time" name="start_time" id="plot-start" value="{{ old('start_time') }}" required>
                @error('start_time') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>

            <div class="ldr-field">
                <label class="ldr-field__label" for="plot-end">{{ __('ui.practicum.plot_end') }}</label>
                <input class="ldr-input" type="time" name="end_time" id="plot-end" value="{{ old('end_time') }}" required>
                @error('end_time') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.practicum.plot_submit') }}</button>
        </form>
    </section>

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

                                    <details class="ldr-inline-edit">
                                        <summary>{{ __('ui.common.edit') }}</summary>
                                        <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="ldr-inline-edit__form">
                                            @csrf
                                            @method('PUT')
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="room-code-{{ $room->id }}">{{ __('ui.practicum.room_form_code') }}</label>
                                                <input class="ldr-input" id="room-code-{{ $room->id }}" name="code" value="{{ $room->code }}" maxlength="32" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="room-name-{{ $room->id }}">{{ __('ui.practicum.room_form_name') }}</label>
                                                <input class="ldr-input" id="room-name-{{ $room->id }}" name="name" value="{{ $room->name }}" maxlength="120" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="room-capacity-{{ $room->id }}">{{ __('ui.practicum.room_form_capacity') }}</label>
                                                <input class="ldr-input" id="room-capacity-{{ $room->id }}" type="number" name="capacity" value="{{ $room->capacity }}" min="1" max="500">
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="room-notes-{{ $room->id }}">{{ __('ui.practicum.room_form_notes') }}</label>
                                                <input class="ldr-input" id="room-notes-{{ $room->id }}" name="notes" value="{{ $room->notes }}" maxlength="255">
                                            </div>
                                            <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--sm">{{ __('ui.practicum.room_edit_submit') }}</button>
                                        </form>
                                    </details>

                                    <form method="POST"
                                          action="{{ route('admin.rooms.destroy', $room) }}"
                                          class="ldr-inline-delete"
                                          onsubmit="return confirm('{{ __('ui.practicum.room_delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ __('ui.common.delete') }}</button>
                                    </form>
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
                                                <form method="POST"
                                                      action="{{ route('admin.practicum.destroy', $slot) }}"
                                                      onsubmit="return confirm('{{ __('ui.practicum.delete_confirm') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ldr-practicum-slot__remove">
                                                        {{ __('ui.common.delete') }}
                                                    </button>
                                                </form>
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

    <section class="ldr-card ldr-room-create">
        <h2 class="ldr-section-title">{{ __('ui.practicum.room_form_title') }}</h2>
        <p class="ldr-muted ldr-section-hint">{{ __('ui.practicum.room_form_hint') }}</p>

        <form method="POST" action="{{ route('admin.rooms.store') }}" class="ldr-room-create__form">
            @csrf
            <div class="ldr-field">
                <label class="ldr-field__label" for="room-code">{{ __('ui.practicum.room_form_code') }}</label>
                <input class="ldr-input" id="room-code" name="code" value="{{ old('code') }}" maxlength="32" required>
                @error('code') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="room-name">{{ __('ui.practicum.room_form_name') }}</label>
                <input class="ldr-input" id="room-name" name="name" value="{{ old('name') }}" maxlength="120" required>
                @error('name') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="room-capacity">{{ __('ui.practicum.room_form_capacity') }}</label>
                <input class="ldr-input" id="room-capacity" type="number" name="capacity" value="{{ old('capacity') }}" min="1" max="500">
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="room-notes">{{ __('ui.practicum.room_form_notes') }}</label>
                <input class="ldr-input" id="room-notes" name="notes" value="{{ old('notes') }}" maxlength="255">
            </div>
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.practicum.room_form_submit') }}</button>
        </form>
        @error('room') <p class="ldr-field-error">{{ $message }}</p> @enderror
    </section>

@endsection
