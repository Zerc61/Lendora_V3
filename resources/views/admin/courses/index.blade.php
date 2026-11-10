@extends('layouts.admin')

@section('title', __('ui.course_quota.title'))

@php
    $statusTones = [
        'kritis' => 'danger',
        'normal' => 'info',
        'surplus' => 'success',
        'unset' => 'neutral',
    ];

    $statusLabels = [
        'kritis' => __('ui.course_quota.status_kritis'),
        'normal' => __('ui.course_quota.status_normal'),
        'surplus' => __('ui.course_quota.status_surplus'),
        'unset' => __('ui.course_quota.status_unset'),
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">{{ __('ui.course_quota.eyebrow') }}</span>
            <h1>{{ __('ui.course_quota.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.course_quota.subtitle') }}</p>
            <span class="ldr-prodi-head__meta">
                <x-icon name="calendar" :size="14" />
                {{ $semester['label'] }}
            </span>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_total') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['total_quota'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_total_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_used') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-list" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['total_used'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_used_hint', ['percent' => $kpis['percent']]) }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_remaining') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['remaining'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_remaining_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.course_quota.kpi_critical') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $kpis['critical'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.course_quota.kpi_critical_hint') }}</span>
        </div>
    </div>

    @if ($courses->isEmpty())
        <x-empty-state
            icon="bar-chart"
            :title="__('ui.course_quota.empty_title')"
            :description="__('ui.course_quota.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.course_quota.col_course') }}</th>
                            <th>{{ __('ui.course_quota.col_lecturer') }}</th>
                            <th>{{ __('ui.course_quota.col_usage') }}</th>
                            <th>{{ __('ui.course_quota.col_remaining') }}</th>
                            <th>{{ __('ui.course_quota.col_status') }}</th>
                            <th>{{ __('ui.course_quota.col_quota') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $row)
                            @php
                                $course = $row['course'];
                                $width = min(100, (int) round($row['percent'] ?? 0));
                            @endphp
                            <tr>
                                <td>
                                    <span class="ldr-table__name">{{ $course->code }} · {{ $course->name }}</span>
                                    <span class="ldr-table__sub">
                                        Semester {{ $course->semester }} · {{ $course->credits }} SKS ·
                                        {{ __('ui.course_quota.sessions', ['count' => $row['sessions']]) }}
                                    </span>

                                    <details class="ldr-inline-edit">
                                        <summary>{{ __('ui.common.edit') }}</summary>
                                        <form method="POST" action="{{ route('admin.courses.update', $course) }}" class="ldr-inline-edit__form">
                                            @csrf
                                            @method('PUT')
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="code-{{ $course->id }}">{{ __('ui.course_quota.form_code') }}</label>
                                                <input class="ldr-input" id="code-{{ $course->id }}" name="code" value="{{ $course->code }}" maxlength="32" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="name-{{ $course->id }}">{{ __('ui.course_quota.form_name') }}</label>
                                                <input class="ldr-input" id="name-{{ $course->id }}" name="name" value="{{ $course->name }}" maxlength="120" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="lecturer-{{ $course->id }}">{{ __('ui.course_quota.form_lecturer') }}</label>
                                                <input class="ldr-input" id="lecturer-{{ $course->id }}" name="lecturer_name" value="{{ $course->lecturer_name }}" maxlength="120">
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="semester-{{ $course->id }}">{{ __('ui.course_quota.form_semester') }}</label>
                                                <input class="ldr-input" id="semester-{{ $course->id }}" type="number" name="semester" value="{{ $course->semester }}" min="1" max="14" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="credits-{{ $course->id }}">{{ __('ui.course_quota.form_credits') }}</label>
                                                <input class="ldr-input" id="credits-{{ $course->id }}" type="number" name="credits" value="{{ $course->credits }}" min="1" max="24" required>
                                            </div>
                                            <div class="ldr-field">
                                                <label class="ldr-field__label" for="quota-edit-{{ $course->id }}">{{ __('ui.course_quota.col_quota') }}</label>
                                                <input class="ldr-input" id="quota-edit-{{ $course->id }}" type="number" name="quota_hours" value="{{ $course->quota_hours }}" min="1" max="9999" placeholder="{{ __('ui.course_quota.quota_placeholder') }}">
                                            </div>
                                            <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--sm">{{ __('ui.course_quota.edit_submit') }}</button>
                                        </form>
                                    </details>

                                    <form method="POST"
                                          action="{{ route('admin.courses.destroy', $course) }}"
                                          class="ldr-inline-delete"
                                          onsubmit="return confirm('{{ __('ui.course_quota.delete_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">{{ __('ui.common.delete') }}</button>
                                    </form>
                                </td>
                                <td>{{ $course->lecturer_name ?: '—' }}</td>
                                <td>
                                    @if ($row['quota_hours'] === null)
                                        <span class="ldr-muted">{{ __('ui.course_quota.quota_unset') }}</span>
                                    @else
                                        <span class="ldr-table__name">
                                            {{ __('ui.course_quota.used_of', ['used' => $row['used_hours'], 'quota' => $row['quota_hours']]) }}
                                        </span>
                                        <div class="ldr-prodi-bar__track" style="margin-top: 4px;">
                                            <span class="ldr-prodi-bar__fill" style="width: {{ $width }}%"></span>
                                        </div>
                                        <span class="ldr-table__sub">{{ $row['percent'] }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row['remaining_hours'] !== null)
                                        {{ __('ui.course_quota.hours', ['hours' => $row['remaining_hours']]) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="ldr-badge ldr-badge--{{ $statusTones[$row['status']] ?? 'neutral' }}">
                                        {{ $statusLabels[$row['status']] ?? $row['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('admin.courses.quota.update', $course) }}"
                                          class="ldr-quota-form">
                                        @csrf
                                        @method('PUT')
                                        <label class="ldr-sr-only" for="quota-{{ $course->id }}">{{ __('ui.course_quota.col_quota') }}</label>
                                        <input class="ldr-input ldr-input--sm"
                                               id="quota-{{ $course->id }}"
                                               type="number"
                                               name="quota_hours"
                                               min="1"
                                               max="9999"
                                               value="{{ $course->quota_hours }}"
                                               placeholder="{{ __('ui.course_quota.quota_placeholder') }}">
                                        <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">
                                            {{ __('ui.common.save') }}
                                        </button>
                                    </form>
                                    @error('quota_hours') <p class="ldr-field-error">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <section class="ldr-card ldr-quota-transfer">
            <h2 class="ldr-section-title">{{ __('ui.course_quota.transfer_title') }}</h2>
            <p class="ldr-muted ldr-section-hint">{{ __('ui.course_quota.transfer_hint') }}</p>

            <form method="POST" action="{{ route('admin.courses.transfer') }}" class="ldr-quota-transfer__form">
                @csrf
                <div class="ldr-field">
                    <label class="ldr-field__label" for="from_course_id">{{ __('ui.course_quota.transfer_from') }}</label>
                    <select class="ldr-input" name="from_course_id" id="from_course_id" required>
                        <option value="">{{ __('ui.course_quota.transfer_choose') }}</option>
                        @foreach ($courses as $row)
                            <option value="{{ $row['course']->id }}" @selected((int) old('from_course_id') === $row['course']->id)>
                                {{ $row['course']->code }} · {{ $row['course']->name }}
                                ({{ $row['course']->quota_hours ?? 0 }} {{ __('ui.course_quota.hours_unit') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ldr-field">
                    <label class="ldr-field__label" for="to_course_id">{{ __('ui.course_quota.transfer_to') }}</label>
                    <select class="ldr-input" name="to_course_id" id="to_course_id" required>
                        <option value="">{{ __('ui.course_quota.transfer_choose') }}</option>
                        @foreach ($courses as $row)
                            <option value="{{ $row['course']->id }}" @selected((int) old('to_course_id') === $row['course']->id)>
                                {{ $row['course']->code }} · {{ $row['course']->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ldr-field">
                    <label class="ldr-field__label" for="hours">{{ __('ui.course_quota.transfer_hours') }}</label>
                    <input class="ldr-input" type="number" name="hours" id="hours" min="1" max="9999"
                           value="{{ old('hours') }}" required>
                </div>

                <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.course_quota.transfer_submit') }}</button>
            </form>

            @error('hours') <p class="ldr-field-error">{{ $message }}</p> @enderror
            @error('to_course_id') <p class="ldr-field-error">{{ $message }}</p> @enderror
            @error('from_course_id') <p class="ldr-field-error">{{ $message }}</p> @enderror
            @error('course') <p class="ldr-field-error">{{ $message }}</p> @enderror
        </section>
    @endif

    <section class="ldr-card ldr-course-create">
        <h2 class="ldr-section-title">{{ __('ui.course_quota.form_title') }}</h2>
        <p class="ldr-muted ldr-section-hint">{{ __('ui.course_quota.form_hint') }}</p>

        <form method="POST" action="{{ route('admin.courses.store') }}" class="ldr-course-create__form">
            @csrf
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-code">{{ __('ui.course_quota.form_code') }}</label>
                <input class="ldr-input" id="new-code" name="code" value="{{ old('code') }}" maxlength="32" required>
                @error('code') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-name">{{ __('ui.course_quota.form_name') }}</label>
                <input class="ldr-input" id="new-name" name="name" value="{{ old('name') }}" maxlength="120" required>
                @error('name') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-lecturer">{{ __('ui.course_quota.form_lecturer') }}</label>
                <input class="ldr-input" id="new-lecturer" name="lecturer_name" value="{{ old('lecturer_name') }}" maxlength="120">
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-semester">{{ __('ui.course_quota.form_semester') }}</label>
                <input class="ldr-input" id="new-semester" type="number" name="semester" value="{{ old('semester', 1) }}" min="1" max="14" required>
                @error('semester') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-credits">{{ __('ui.course_quota.form_credits') }}</label>
                <input class="ldr-input" id="new-credits" type="number" name="credits" value="{{ old('credits', 3) }}" min="1" max="24" required>
                @error('credits') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <div class="ldr-field">
                <label class="ldr-field__label" for="new-quota">{{ __('ui.course_quota.col_quota') }}</label>
                <input class="ldr-input" id="new-quota" type="number" name="quota_hours" value="{{ old('quota_hours') }}" min="1" max="9999" placeholder="{{ __('ui.course_quota.quota_placeholder') }}">
                @error('quota_hours') <p class="ldr-field-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="ldr-btn ldr-btn--primary">{{ __('ui.course_quota.form_submit') }}</button>
        </form>
    </section>
@endsection
