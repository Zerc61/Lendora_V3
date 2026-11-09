@extends('layouts.admin')

@section('title', __('ui.maintenance.finish'))

@php
    $asset = $maintenance->asset;
    $photo = $asset?->photos->first();
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.maintenances.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.maintenance.back_to_list') }}
    </a>

    <section class="ldr-booking-hero">
        <div class="ldr-booking-hero__main">
            <div class="ldr-booking-hero__tags">
                <span class="ldr-mono ldr-booking-hero__code">{{ $asset?->code }}</span>
                <x-status-badge type="maintenance" :status="$maintenance->status->value" />
            </div>

            <h1>{{ $asset?->name ?? '—' }}</h1>

            <p class="ldr-booking-hero__meta">
                {{ collect([$asset?->brand, $asset?->model])->filter()->join(' · ') ?: '—' }}
            </p>

            <div class="ldr-booking-hero__facts">
                <span>
                    <x-icon name="user" :size="15" />
                    {{ $maintenance->reportedBy?->name ?? '—' }}
                </span>
                <span>
                    <x-icon name="wrench" :size="15" />
                    {{ $maintenance->assignedTo?->name ?? __('ui.maintenance.unassigned') }}
                </span>
                @if ($maintenance->started_at)
                    <span>
                        <x-icon name="calendar" :size="15" />
                        {{ $maintenance->started_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                    </span>
                @endif
            </div>
        </div>

        <span class="ldr-workorder__media ldr-workorder__media--lg">
            @if ($photo)
                <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                     alt="{{ $asset->name }}"
                     loading="lazy"
                     width="96"
                     height="96">
            @else
                <x-icon name="camera" :size="28" />
            @endif
        </span>
    </section>

    <div class="ldr-detail-grid">
        <div class="ldr-detail-grid__main">
            <section class="ldr-card">
                <h2>{{ __('ui.maintenance.work_order') }}</h2>

                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.maintenance.description') }}</dt>
                        <dd>{{ $maintenance->description }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.reported_by') }}</dt>
                        <dd>{{ $maintenance->reportedBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.started_at') }}</dt>
                        <dd>{{ $maintenance->started_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            <form method="POST" action="{{ route('admin.maintenances.finish.store', $maintenance) }}" class="ldr-card">
                @csrf

                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.maintenance.finish') }}</h2>
                        <p>{{ __('ui.maintenance.finish_hint') }}</p>
                    </div>
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="result_notes">{{ __('ui.maintenance.result_notes') }}</label>
                    <textarea id="result_notes" class="ldr-input" name="result_notes">{{ old('result_notes', $maintenance->result_notes) }}</textarea>
                    @error('result_notes') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <div class="ldr-field">
                    <label class="ldr-label" for="cost">{{ __('ui.maintenance.cost') }}</label>
                    <input id="cost" class="ldr-input" type="number" name="cost" min="0" value="{{ old('cost', $maintenance->cost) }}">
                    @error('cost') <p class="ldr-field__error">{{ $message }}</p> @enderror
                </div>

                <label class="ldr-checkbox" for="condition_good">
                    <input id="condition_good" type="checkbox" name="condition_good" value="1">
                    {{ __('ui.maintenance.condition_good') }}
                </label>

                <div class="ldr-form-actions">
                    <button type="submit" class="ldr-btn ldr-btn--primary">
                        <x-icon name="check-circle" :size="18" />
                        {{ __('ui.maintenance.save_result') }}
                    </button>
                    <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.maintenances.index') }}">{{ __('ui.common.cancel') }}</a>
                </div>
            </form>
        </div>

        <aside class="ldr-detail-grid__side">
            <section class="ldr-card">
                <h2>{{ __('ui.maintenance.summary') }}</h2>

                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.maintenance.status') }}</dt>
                        <dd><x-status-badge type="maintenance" :status="$maintenance->status->value" /></dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.technician') }}</dt>
                        <dd>{{ $maintenance->assignedTo?->name ?? __('ui.maintenance.unassigned') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.cost') }}</dt>
                        <dd>
                            {{ $maintenance->cost !== null ? 'Rp '.number_format($maintenance->cost, 0, ',', '.') : '—' }}
                        </dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
