@extends('layouts.admin')

@section('title', __('ui.gantt.title'))

@php
    $start = $gantt['start'];
    $dayCount = $gantt['days'];
    $columns = collect(range(0, $dayCount - 1))->map(fn (int $i) => $start->copy()->addDays($i));
    $groups = collect($gantt['rows'])->groupBy(fn (array $row) => $row['asset']->category?->name ?? '—');
    $conflictRows = collect($gantt['rows'])->where('conflict', true);
    $idleRows = collect($gantt['rows'])->filter(fn (array $row) => $row['bars']->isEmpty());
    $weekdays = __('ui.booking.weekdays');

    $exportQuery = array_filter([
        'date_from' => $start->toDateString(),
        'days' => $dayCount,
        'category_id' => $filters['category_id'] ?? null,
    ]);

    $monthMode = ! in_array((int) ($filters['days'] ?? 0), [14, 30], true);

    $periodOptions = [
        ['days' => 14, 'label' => __('ui.gantt.period_14')],
        ['days' => 30, 'label' => __('ui.gantt.period_30')],
        ['days' => 0, 'label' => __('ui.gantt.period_month')],
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">O2 · {{ __('ui.gantt.title') }}</span>
            <h1>{{ __('ui.gantt.title') }}</h1>
            <p class="ldr-muted">{{ __('ui.gantt.subtitle') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
               href="{{ route('admin.bookings.calendar.export', $exportQuery + ['format' => 'csv']) }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.gantt.export_csv') }}
            </a>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
               href="{{ route('admin.bookings.calendar.export', $exportQuery + ['format' => 'xlsx']) }}">
                <x-icon name="bar-chart" :size="16" />
                {{ __('ui.common.download_xlsx') }}
            </a>
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
               href="{{ route('admin.bookings.calendar.export', $exportQuery + ['format' => 'ics']) }}">
                <x-icon name="calendar" :size="16" />
                {{ __('ui.gantt.export_ics') }}
            </a>
            <a class="ldr-btn ldr-btn--primary ldr-btn--sm" href="{{ route('admin.bookings.walk-in') }}">
                <x-icon name="plus" :size="16" />
                {{ __('ui.gantt.walk_in') }}
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.gantt.occupancy') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="activity" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ number_format((float) $gantt['kpis']['occupancy'], 1, ',', '.') }}%</span>
            <span class="ldr-kpi__hint">{{ __('ui.gantt.occupancy_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--info">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.gantt.active_outside') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="layers" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $gantt['kpis']['active_outside'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.gantt.active_outside_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.gantt.pending_handover') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="arrow-left-right" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $gantt['kpis']['pending_handover'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.gantt.pending_handover_hint') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.gantt.conflicts') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="alert-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $gantt['kpis']['conflicts'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.gantt.conflicts_hint') }}</span>
        </div>
    </div>

    @if ($conflictRows->isNotEmpty())
        <div class="ldr-gantt-alert" role="alert">
            <div>
                <strong>{{ __('ui.gantt.conflict_banner') }}</strong>
                <p>
                    @foreach ($conflictRows as $row)
                        {{ $row['asset']->code }}@if (! $loop->last), @endif
                    @endforeach
                </p>
            </div>

            <a class="ldr-btn ldr-btn--danger ldr-btn--sm" href="{{ route('admin.bookings.approval') }}">
                {{ __('ui.gantt.resolve') }}
            </a>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.bookings.calendar') }}" class="ldr-gantt-toolbar">
        <div class="ldr-tab-bar ldr-tab-bar--inline">
            @foreach ($periodOptions as $option)
                <a class="ldr-tab {{ ($option['days'] === 0 ? $monthMode : (! $monthMode && (int) $filters['days'] === $option['days'])) ? 'is-active' : '' }}"
                   href="{{ route('admin.bookings.calendar', array_filter(['days' => $option['days'] ?: null, 'month' => $option['days'] ? null : $filters['month'], 'category_id' => $filters['category_id'] ?? null])) }}">
                    {{ $option['label'] }}
                </a>
            @endforeach
        </div>

        <div class="ldr-filter-bar ldr-filter-bar--compact">
            <div class="ldr-filter-bar__select">
                <label class="ldr-sr-only" for="gantt-category">{{ __('ui.asset.fields.category') }}</label>
                <select id="gantt-category" class="ldr-input" name="category_id" data-auto-submit-field>
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="days" value="{{ $dayCount }}">
            <input type="hidden" name="date_from" value="{{ $start->toDateString() }}">

            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
        </div>
    </form>

    <div class="ldr-gantt-legend">
        <span><i class="is-approved"></i> {{ __('ui.booking_status.approved') }}</span>
        <span><i class="is-active"></i> {{ __('ui.booking_status.active') }}</span>
        <span><i class="is-pending"></i> {{ __('ui.booking_status.pending') }}</span>
        <span><i class="is-block"></i> {{ __('ui.gantt.maintenance') }}</span>
        <span><i class="is-conflict"></i> {{ __('ui.gantt.conflict_legend') }}</span>
        <span><i class="is-free"></i> {{ __('ui.gantt.free_legend') }}</span>
    </div>

    @if ($gantt['rows']->isEmpty())
        <x-empty-state
            icon="calendar"
            :title="__('ui.asset.empty_title')"
            :description="__('ui.booking.calendar_empty')"
        />
    @else
        <div class="ldr-gantt-layout">
            <div class="ldr-gantt-card">
                <div class="ldr-gantt-scroll">
                    <div class="ldr-gantt" style="--gantt-days: {{ $dayCount }};">
                        <div class="ldr-gantt__corner">
                            <span>{{ __('ui.gantt.unit_label') }}</span>
                            <strong>{{ __('ui.gantt.unit_count', ['count' => $gantt['rows']->count()]) }}</strong>
                        </div>

                        <div class="ldr-gantt__days">
                            @foreach ($columns as $day)
                                <div class="ldr-gantt__day {{ $day->isToday() ? 'is-today' : '' }}">
                                    <span>{{ $weekdays[$day->dayOfWeekIso - 1] ?? '' }}</span>
                                    <strong>{{ $day->day }}</strong>
                                </div>
                            @endforeach
                        </div>

                        @foreach ($groups as $categoryName => $rows)
                            <div class="ldr-gantt__group">
                                <x-icon name="package" :size="16" />
                                {{ $categoryName }}
                                <span>{{ __('ui.gantt.unit_count', ['count' => $rows->count()]) }}</span>
                            </div>
                            <div class="ldr-gantt__group-line"></div>

                            @foreach ($rows as $row)
                                @php($asset = $row['asset'])

                                <div class="ldr-gantt__label">
                                    <span class="ldr-mono">{{ $asset->code }}</span>
                                    <strong>{{ $asset->name }}</strong>
                                    <span class="ldr-muted">{{ $asset->location ?: '—' }}</span>
                                </div>

                                <div class="ldr-gantt__track {{ $row['conflict'] ? 'is-conflict' : '' }}">
                                    @foreach ($columns as $day)
                                        <span class="ldr-gantt__cell {{ $day->isToday() ? 'is-today' : '' }}"></span>
                                    @endforeach

                                    @foreach ($row['bars'] as $bar)
                                        @if ($bar['type'] === 'booking')
                                            <a class="ldr-gantt__bar ldr-gantt__bar--{{ $bar['status'] }} {{ $bar['conflict'] ? 'is-conflict' : '' }}"
                                               href="{{ route('admin.bookings.show', $bar['booking_id']) }}"
                                               style="grid-column: {{ $bar['offset'] + 1 }} / span {{ $bar['length'] }};"
                                               data-gantt-bar
                                               data-code="{{ $bar['code'] }}"
                                               data-asset="{{ $asset->code }}"
                                               data-name="{{ $asset->name }}"
                                               data-start="{{ $bar['start'] }}"
                                               data-end="{{ $bar['end'] }}"
                                               data-status="{{ __('ui.booking_status.'.$bar['status']) }}"
                                               data-url="{{ route('admin.bookings.show', $bar['booking_id']) }}"
                                               title="{{ $asset->code }} · {{ $bar['code'] }}">
                                                {{ $bar['code'] }}
                                            </a>
                                        @else
                                            <span class="ldr-gantt__bar ldr-gantt__bar--block"
                                                  style="grid-column: {{ $bar['offset'] + 1 }} / span {{ $bar['length'] }};"
                                                  data-gantt-bar
                                                  data-code="{{ $bar['code'] }}"
                                                  data-asset="{{ $asset->code }}"
                                                  data-name="{{ $asset->name }}"
                                                  data-start="{{ $bar['start'] }}"
                                                  data-end="{{ $bar['end'] }}"
                                                  data-status="{{ __('ui.gantt.maintenance') }}"
                                                  title="{{ $asset->code }} · {{ $bar['code'] }}">
                                                {{ $bar['code'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="ldr-gantt-side">
                <section class="ldr-card" data-gantt-panel>
                    <div class="ldr-card-head ldr-card-head--flush">
                        <div>
                            <h2>{{ __('ui.gantt.detail_title') }}</h2>
                            <p>{{ __('ui.gantt.detail_hint') }}</p>
                        </div>
                    </div>

                    <dl class="ldr-spec-list">
                        <div>
                            <dt>{{ __('ui.booking.code') }}</dt>
                            <dd class="ldr-mono" data-gantt-field="code">{{ __('ui.gantt.detail_empty') }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.nav.assets') }}</dt>
                            <dd data-gantt-field="asset">—</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.booking.period') }}</dt>
                            <dd data-gantt-field="period">—</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.booking.filter_status') }}</dt>
                            <dd data-gantt-field="status">—</dd>
                        </div>
                    </dl>

                    <a class="ldr-btn ldr-btn--primary ldr-btn--block" href="#" data-gantt-link hidden>
                        {{ __('ui.gantt.open_booking') }}
                    </a>
                </section>

                <section class="ldr-card">
                    <h2>{{ __('ui.gantt.idle_title') }}</h2>

                    @if ($idleRows->isEmpty())
                        <p class="ldr-muted">{{ __('ui.gantt.idle_empty') }}</p>
                    @else
                        <div class="ldr-standby-list">
                            @foreach ($idleRows as $row)
                                <div class="ldr-standby">
                                    <x-icon name="package" :size="18" />
                                    <div>
                                        <strong>{{ $row['asset']->name }}</strong>
                                        <span class="ldr-mono">{{ $row['asset']->code }}</span>
                                    </div>
                                    <span class="ldr-badge ldr-badge--success">{{ __('ui.gantt.free_legend') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    @endif
@endsection
