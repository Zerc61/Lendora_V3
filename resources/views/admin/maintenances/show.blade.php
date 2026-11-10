@extends('layouts.admin')

@section('title', __('ui.maintenance.ticket_summary'))

@php
    $priority = $maintenance->priority?->value ?? 'normal';
    $timeline = [];

    $timeline[] = [
        'label' => __('ui.maintenance.ticket_summary'),
        'at' => $maintenance->created_at,
        'by' => $maintenance->reportedBy?->name,
        'done' => true,
    ];

    foreach ((array) ($maintenance->progress ?? []) as $step) {
        $timeline[] = [
            'label' => $step['label'] ?? '—',
            'at' => isset($step['at']) ? \Illuminate\Support\Carbon::parse($step['at']) : null,
            'by' => optional(\App\Models\User::find($step['by'] ?? null))->name,
            'done' => (bool) ($step['done'] ?? false),
        ];
    }

    if ($maintenance->started_at) {
        $timeline[] = [
            'label' => __('ui.maintenance.started_at'),
            'at' => $maintenance->started_at,
            'by' => $maintenance->assignedTo?->name,
            'done' => true,
        ];
    }

    if ($maintenance->finished_at) {
        $timeline[] = [
            'label' => __('ui.maintenance_status.done'),
            'at' => $maintenance->finished_at,
            'by' => $maintenance->assignedTo?->name,
            'done' => true,
        ];
    }

    $inspection = $maintenance->inspections->first();
    $penalty = $maintenance->penalties->first();
    $partsTotal = $maintenance->partsTotal();
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.maintenances.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.maintenance.back_to_list') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">W10 · {{ __('ui.nav.maintenances') }}</span>
            <h1 class="ldr-mono">{{ $maintenance->code ?? 'MTC-'.$maintenance->id }}</h1>
            <p class="ldr-muted">
                {{ $maintenance->asset?->code }} · {{ $maintenance->asset?->name }} ·
                {{ $maintenance->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB
            </p>
        </div>

        <div class="ldr-page-head__actions">
            <span class="ldr-badge ldr-badge--priority-{{ $priority }}">
                {{ __('ui.maintenance_priority.'.$priority) }}
            </span>
            <x-status-badge type="maintenance" :status="$maintenance->status->value" />
        </div>
    </div>

    <div class="ldr-handover-grid">
        <div class="ldr-handover-form">
            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.maintenance.ticket_summary') }}</h2>
                        <p>{{ __('ui.maintenance.description') }}</p>
                    </div>
                </div>

                <div class="ldr-unit__head">
                    <span class="ldr-unit__media">
                        @if ($maintenance->asset?->photos->first())
                            <img src="{{ Illuminate\Support\Facades\Storage::url($maintenance->asset->photos->first()->path) }}"
                                 alt="{{ $maintenance->asset->name }}"
                                 loading="lazy"
                                 width="72"
                                 height="72">
                        @else
                            <x-icon name="camera" :size="24" />
                        @endif
                    </span>
                    <div class="ldr-handover-item__body">
                        <span class="ldr-mono">{{ $maintenance->asset?->code }}</span>
                        <strong>{{ $maintenance->asset?->name ?? '—' }}</strong>
                        <span class="ldr-muted">
                            {{ collect([$maintenance->asset?->brand, $maintenance->asset?->model])->filter()->join(' · ') ?: '—' }}
                        </span>
                    </div>
                </div>

                <dl class="ldr-spec-list ldr-spec-list--two">
                    <div>
                        <dt>{{ __('ui.maintenance.source') }}</dt>
                        <dd>
                            @if ($inspection)
                                <a class="ldr-link-sm" href="{{ route('admin.inspections.show', $inspection) }}">
                                    {{ __('ui.maintenance.source_inspection') }} {{ $inspection->code ?? '' }}
                                </a>
                            @elseif ($penalty)
                                <a class="ldr-link-sm" href="{{ route('admin.penalties.show', $penalty) }}">
                                    {{ __('ui.maintenance.source_penalty') }}
                                </a>
                            @else
                                {{ __('ui.maintenance.source_manual') }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.reported_at') }}</dt>
                        <dd>{{ $maintenance->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.reported_by') }}</dt>
                        <dd>{{ $maintenance->reportedBy?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.due_at') }}</dt>
                        <dd>{{ $maintenance->due_at?->copy()->locale('id')->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.technician') }}</dt>
                        <dd>{{ $maintenance->assignedTo?->name ?? __('ui.maintenance.unassigned') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.assignment') }}</dt>
                        <dd>{{ $maintenance->vendor ?: '—' }}</dd>
                    </div>
                </dl>

                <div class="ldr-inspection-notes">
                    <h3>{{ __('ui.maintenance.description') }}</h3>
                    <p>{{ $maintenance->description }}</p>
                </div>

                @if ($maintenance->result_notes)
                    <div class="ldr-inspection-notes">
                        <h3>{{ __('ui.maintenance.result_notes') }}</h3>
                        <p>{{ $maintenance->result_notes }}</p>
                    </div>
                @endif
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.maintenance.parts') }}</h2>
                        <p>{{ __('ui.maintenance.parts_subtotal') }}</p>
                    </div>
                </div>

                @if ($maintenance->parts->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.common.no_data') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.maintenance.part_name') }}</th>
                                    <th>{{ __('ui.maintenance.part_code') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.maintenance.qty') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.maintenance.unit_cost') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.maintenance.subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($maintenance->parts as $part)
                                    <tr>
                                        <td>{{ $part->name }}</td>
                                        <td class="ldr-mono">{{ $part->part_code ?: '—' }}</td>
                                        <td class="ldr-table__num">{{ $part->quantity }}</td>
                                        <td class="ldr-table__num">Rp {{ number_format($part->unit_cost, 0, ',', '.') }}</td>
                                        <td class="ldr-table__num">Rp {{ number_format($part->subtotal(), 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <dl class="ldr-cost-summary">
                    <div>
                        <dt>{{ __('ui.maintenance.parts_subtotal') }}</dt>
                        <dd>Rp {{ number_format($partsTotal, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.maintenance.labor_cost') }}</dt>
                        <dd>Rp {{ number_format($maintenance->labor_cost ?? 0, 0, ',', '.') }}</dd>
                    </div>
                    <div class="ldr-cost-summary__total">
                        <dt>{{ __('ui.maintenance.total_bill') }}</dt>
                        <dd>Rp {{ number_format($maintenance->totalCost(), 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if (in_array($role, ['operator', 'organization_admin', 'technician'], true)
                    && ! in_array($maintenance->status, [\App\Enums\MaintenanceStatus::Done, \App\Enums\MaintenanceStatus::Cancelled], true))
                    <form method="POST" action="{{ route('admin.maintenances.parts.store', $maintenance) }}" class="ldr-parts-form">
                        @csrf
                        <h3>{{ __('ui.maintenance.add_part') }}</h3>
                        <div class="ldr-hv-form-grid">
                            <label class="ldr-field">
                                <span>{{ __('ui.maintenance.part_name') }}</span>
                                <input class="ldr-input" type="text" name="name" required maxlength="150">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.maintenance.part_code') }}</span>
                                <input class="ldr-input" type="text" name="part_code" maxlength="60">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.maintenance.qty') }}</span>
                                <input class="ldr-input" type="number" name="quantity" value="1" min="1" max="999" required>
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.maintenance.unit_cost') }}</span>
                                <input class="ldr-input" type="number" name="unit_cost" value="0" min="0" required>
                            </label>
                        </div>
                        <button class="ldr-btn ldr-btn--secondary" type="submit">{{ __('ui.common.save') }}</button>
                    </form>
                @endif
            </section>
        </div>

        <aside class="ldr-handover-side">
            <section class="ldr-card">
                <h2>{{ __('ui.maintenance.progress_title') }}</h2>

                <ol class="ldr-progress">
                    @foreach ($timeline as $step)
                        <li class="ldr-progress__item {{ $step['done'] ? 'is-done' : '' }}">
                            <span class="ldr-progress__dot" aria-hidden="true"></span>
                            <div>
                                <strong>{{ $step['label'] }}</strong>
                                <span class="ldr-muted">
                                    @if ($step['at'])
                                        {{ $step['at']->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB
                                    @endif
                                    @if ($step['by'])
                                        · {{ $step['by'] }}
                                    @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>

                @if ($maintenance->final_condition)
                    <p class="ldr-muted">
                        {{ __('ui.maintenance.final_condition') }}:
                        {{ __('ui.condition.'.($maintenance->final_condition === 'good' ? 'good' : $maintenance->final_condition)) }}
                    </p>
                @endif
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.maintenance.actions_title') }}</h2>

                <div class="ldr-stack">
                    @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::Scheduled)
                        <form method="POST" action="{{ route('admin.maintenances.start', $maintenance) }}">
                            @csrf
                            <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block">
                                <x-icon name="wrench" :size="16" />
                                {{ __('ui.maintenance.start') }}
                            </button>
                        </form>
                    @endif

                    @if ($role === 'technician' && $maintenance->status === \App\Enums\MaintenanceStatus::InProgress)
                        <a class="ldr-btn ldr-btn--primary ldr-btn--block" href="{{ route('admin.maintenances.finish', $maintenance) }}">
                            <x-icon name="check-circle" :size="16" />
                            {{ __('ui.maintenance.finish') }}
                        </a>
                    @endif

                    @if ($role === 'organization_admin' && ! in_array($maintenance->status, [\App\Enums\MaintenanceStatus::Done, \App\Enums\MaintenanceStatus::Cancelled], true))
                        <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.maintenances.edit', $maintenance) }}">
                            {{ __('ui.maintenance.edit') }}
                        </a>
                        <form method="POST" action="{{ route('admin.maintenances.destroy', $maintenance) }}"
                              data-confirm="{{ __('ui.maintenance.cancel_confirm') }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="ldr-btn ldr-btn--ghost ldr-btn--block">
                                {{ __('ui.maintenance.cancel_task') }}
                            </button>
                        </form>
                    @endif
                </div>
            </section>

            @if ($inspection || $penalty)
                <section class="ldr-card">
                    <h2>{{ __('ui.maintenance.source') }}</h2>
                    @if ($inspection)
                        <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.inspections.show', $inspection) }}">
                            {{ __('ui.maintenance.view_inspection') }}
                        </a>
                    @endif
                    @if ($penalty)
                        <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.penalties.show', $penalty) }}">
                            {{ __('ui.maintenance.view_penalty') }}
                        </a>
                    @endif
                </section>
            @endif
        </aside>
    </div>
@endsection
