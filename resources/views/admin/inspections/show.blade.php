@extends('layouts.admin')

@section('title', __('ui.inspection.detail_title'))

@php
    $checklistLabels = [
        'shutter_sensor' => __('ui.inspection.check_shutter'),
        'ports' => __('ui.inspection.check_port'),
        'lcd_evf' => __('ui.inspection.check_lcd'),
        'completeness' => __('ui.inspection.check_completeness'),
    ];
    $photos = (array) ($inspection->photo_paths ?? []);
    $resultBadge = match ($inspection->result) {
        App\Enums\InspectionResult::Ok => 'ldr-badge--success',
        App\Enums\InspectionResult::NeedsRepair => 'ldr-badge--warning',
        default => 'ldr-badge--danger',
    };
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.inspections.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.inspection.history_title') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W9 · {{ __('ui.inspection.detail_title') }}</span>
            <h1 class="ldr-mono">{{ $inspection->code ?? '#'.$inspection->id }}</h1>
            <p class="ldr-muted">
                {{ $inspection->asset?->code }} · {{ $inspection->asset?->name }} ·
                {{ $inspection->checked_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB
            </p>
        </div>

        <div class="ldr-page-head__actions">
            <span class="ldr-badge {{ $resultBadge }}">{{ __('ui.inspection_result.'.$inspection->result->value) }}</span>
            @if ($inspection->asset)
                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.assets.index', ['search' => $inspection->asset->code]) }}">
                    {{ __('ui.nav.assets') }}
                </a>
            @endif
        </div>
    </div>

    <div class="ldr-handover-grid">
        <div class="ldr-handover-form">
            <section class="ldr-card">
                <div class="ldr-unit__head">
                    <span class="ldr-unit__media">
                        @if ($inspection->asset?->photos->first())
                            <img src="{{ Illuminate\Support\Facades\Storage::url($inspection->asset->photos->first()->path) }}"
                                 alt="{{ $inspection->asset->name }}"
                                 loading="lazy"
                                 width="72"
                                 height="72">
                        @else
                            <x-icon name="camera" :size="24" />
                        @endif
                    </span>
                    <div class="ldr-handover-item__body">
                        <span class="ldr-mono">{{ $inspection->asset?->code }}</span>
                        <strong>{{ $inspection->asset?->name ?? '—' }}</strong>
                        <span class="ldr-muted">
                            {{ collect([$inspection->asset?->brand, $inspection->asset?->model])->filter()->join(' · ') ?: '—' }}
                        </span>
                    </div>
                </div>

                <dl class="ldr-spec-list ldr-spec-list--two">
                    <div>
                        <dt>{{ __('ui.inspection.context') }}</dt>
                        <dd>{{ $inspection->context?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.result') }}</dt>
                        <dd>{{ __('ui.inspection_result.'.$inspection->result->value) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.inspector') }}</dt>
                        <dd>{{ $inspection->inspector?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.checked_at') }}</dt>
                        <dd>{{ $inspection->checked_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.score') }}</dt>
                        <dd>{{ $inspection->condition_score !== null ? $inspection->condition_score.' / 100' : '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.follow_up') }}</dt>
                        <dd>
                            @if ($inspection->maintenance)
                                <a class="ldr-link-sm ldr-mono" href="{{ route('admin.maintenances.show', $inspection->maintenance) }}">
                                    {{ $inspection->maintenance->code ?? $inspection->maintenance->id }}
                                </a>
                            @else
                                {{ __('ui.inspection.follow_up_none') }}
                            @endif
                        </dd>
                    </div>
                    @if ($inspection->bookingItem?->booking)
                        <div>
                            <dt>{{ __('ui.inspection.linked_booking') }}</dt>
                            <dd>
                                <a class="ldr-link-sm ldr-mono" href="{{ route('admin.bookings.show', $inspection->bookingItem->booking) }}">
                                    {{ $inspection->bookingItem->booking->code }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.inspection.checklist') }}</h2>
                        <p>{{ __('ui.inspection.checklist_hint') }}</p>
                    </div>
                </div>

                @if (empty($inspection->checklist))
                    <p class="ldr-dash-empty">{{ __('ui.inspection.no_checklist') }}</p>
                @else
                    <ul class="ldr-checklist-result">
                        @foreach ($inspection->checklist as $key => $passed)
                            <li class="{{ $passed ? 'is-pass' : 'is-fail' }}">
                                <x-icon :name="$passed ? 'check-circle' : 'x'" :size="18" />
                                <span>{{ $checklistLabels[$key] ?? $key }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="ldr-inspection-notes">
                    <h3>{{ __('ui.inspection.notes_title') }}</h3>
                    <p>{{ $inspection->notes ?: '—' }}</p>
                </div>
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.inspection.photos') }}</h2>
                    </div>
                </div>

                @if (empty($photos))
                    <p class="ldr-dash-empty">{{ __('ui.inspection.no_photos') }}</p>
                @else
                    <div class="ldr-photo-grid">
                        @foreach ($photos as $path)
                            <a class="ldr-photo" href="{{ Illuminate\Support\Facades\Storage::url($path) }}" target="_blank" rel="noopener">
                                <img src="{{ Illuminate\Support\Facades\Storage::url($path) }}" alt="{{ __('ui.inspection.photos') }}" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="ldr-handover-side">
            @if ($inspection->maintenance)
                <section class="ldr-card">
                    <h2>{{ __('ui.inspection.linked_maintenance') }}</h2>
                    <p class="ldr-mono">{{ $inspection->maintenance->code ?? '—' }}</p>
                    <x-status-badge type="maintenance" :status="$inspection->maintenance->status->value" />
                    <p class="ldr-muted">{{ \Illuminate\Support\Str::limit($inspection->maintenance->description, 120) }}</p>
                    <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.maintenances.show', $inspection->maintenance) }}">
                        {{ __('ui.inspection.detail') }}
                    </a>
                </section>
            @endif

            <section class="ldr-card ldr-hv-sop">
                <h2>{{ __('ui.inspection.flow_title') }}</h2>
                <ol>
                    <li>{{ __('ui.inspection.flow_1') }}</li>
                    <li>{{ __('ui.inspection.flow_2') }}</li>
                    <li>{{ __('ui.inspection.flow_3') }}</li>
                </ol>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.inspection.officer_title') }}</h2>
                <div class="ldr-hv-ident__person">
                    <span class="ldr-hv-avatar" aria-hidden="true">
                        {{ mb_substr($inspection->inspector?->name ?? '?', 0, 2) }}
                    </span>
                    <div>
                        <strong>{{ $inspection->inspector?->name ?? '—' }}</strong>
                        <span class="ldr-muted">{{ $inspection->inspector?->role?->name }}</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
@endsection
