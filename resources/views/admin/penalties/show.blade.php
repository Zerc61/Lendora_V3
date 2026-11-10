@extends('layouts.admin')

@section('title', $penalty->bap_number)

@php
    $photos = (array) ($penalty->photo_paths ?? []);
    $paid = $penalty->paidAmount();
    $remaining = $penalty->remainingAmount();
    $accountActive = $penalty->borrower?->status === 'active';
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.penalties.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.penalty.penalties') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">O3 · {{ __('ui.penalty.bap_title') }}</span>
            <h1 class="ldr-mono">{{ $penalty->bap_number }}</h1>
            <p class="ldr-muted">
                {{ $penalty->borrower?->name ?? '—' }} ·
                {{ $penalty->asset?->code ?: '—' }} ·
                {{ $penalty->incident_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB
            </p>
        </div>

        <div class="ldr-page-head__actions">
            <x-status-badge type="penalty" :status="$penalty->status->value" />
            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.penalties.print', $penalty) }}">
                <x-icon name="printer" :size="16" />
                {{ __('ui.penalty.print_bap') }}
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="ldr-alert ldr-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="ldr-handover-grid">
        <div class="ldr-handover-form">
            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.damage_finding') }}</h2>
                        <p>{{ __('ui.penalty.damage_desc') }}</p>
                    </div>
                </div>

                <div class="ldr-unit__head">
                    <span class="ldr-unit__media">
                        @if ($penalty->asset?->photos->first())
                            <img src="{{ Illuminate\Support\Facades\Storage::url($penalty->asset->photos->first()->path) }}"
                                 alt="{{ $penalty->asset->name }}"
                                 loading="lazy"
                                 width="72"
                                 height="72">
                        @else
                            <x-icon name="camera" :size="24" />
                        @endif
                    </span>
                    <div class="ldr-handover-item__body">
                        <span class="ldr-mono">{{ $penalty->asset?->code ?: '—' }}</span>
                        <strong>{{ $penalty->asset?->name ?: '—' }}</strong>
                        <span class="ldr-muted">
                            {{ collect([$penalty->asset?->brand, $penalty->asset?->model])->filter()->join(' · ') ?: '—' }}
                        </span>
                    </div>
                </div>

                <dl class="ldr-spec-list ldr-spec-list--two">
                    <div>
                        <dt>{{ __('ui.penalty.case_category') }}</dt>
                        <dd>{{ $penalty->case_category ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.incident_date') }}</dt>
                        <dd>{{ $penalty->incident_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.inspector') }}</dt>
                        <dd>{{ $penalty->inspector_name }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.issued_at') }}</dt>
                        <dd>{{ $penalty->issued_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? __('ui.penalty.not_issued') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.due') }}</dt>
                        <dd>{{ $penalty->due_at?->copy()->locale('id')->translatedFormat('d M Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.inspection.linked_maintenance') }}</dt>
                        <dd>
                            @if ($penalty->maintenance)
                                <a class="ldr-link-sm ldr-mono" href="{{ route('admin.maintenances.show', $penalty->maintenance) }}">
                                    {{ $penalty->maintenance->code ?? $penalty->maintenance->id }}
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="ldr-inspection-notes">
                    <h3>{{ __('ui.penalty.damage_desc') }}</h3>
                    <p>{{ $penalty->damage_description }}</p>
                </div>

                @if ($penalty->chronology)
                    <div class="ldr-inspection-notes">
                        <h3>{{ __('ui.penalty.chronology') }}</h3>
                        <p>{{ $penalty->chronology }}</p>
                    </div>
                @endif
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.borrower_data') }}</h2>
                        <p>{{ __('ui.penalty.account_status') }}</p>
                    </div>
                </div>

                <div class="ldr-hv-ident__person">
                    <span class="ldr-hv-avatar" aria-hidden="true">
                        {{ mb_substr($penalty->borrower?->name ?? '?', 0, 2) }}
                    </span>
                    <div>
                        <strong>{{ $penalty->borrower?->name ?? '—' }}</strong>
                        <span class="ldr-muted">{{ $penalty->borrower?->nim ? 'NIM '.$penalty->borrower->nim : $penalty->borrower?->email }}</span>
                    </div>
                    <span class="ldr-badge {{ $accountActive ? 'ldr-badge--success' : 'ldr-badge--danger' }}">
                        {{ $accountActive ? __('ui.user.active') : __('ui.user.suspended') }}
                    </span>
                </div>

                <dl class="ldr-spec-list ldr-spec-list--two">
                    <div>
                        <dt>{{ __('ui.penalty.compliance_score') }}</dt>
                        <dd>{{ $penalty->borrower?->compliance_score ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.detail') }}</dt>
                        <dd>
                            @if ($penalty->booking)
                                <a class="ldr-link-sm ldr-mono" href="{{ route('admin.bookings.show', $penalty->booking) }}">
                                    {{ $penalty->booking->code }}
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            @if ($photos !== [])
                <section class="ldr-card">
                    <div class="ldr-card-head ldr-card-head--flush">
                        <div>
                            <h2>{{ __('ui.penalty.photos_title') }}</h2>
                        </div>
                    </div>

                    <div class="ldr-photo-grid">
                        @foreach ($photos as $path)
                            <a class="ldr-photo" href="{{ Illuminate\Support\Facades\Storage::url($path) }}" target="_blank" rel="noopener">
                                <img src="{{ Illuminate\Support\Facades\Storage::url($path) }}" alt="{{ __('ui.penalty.photos_title') }}" loading="lazy">
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.breakdown') }}</h2>
                        <p>{{ __('ui.penalty.price_breakdown') }}</p>
                    </div>
                </div>

                @if ($penalty->items->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.common.no_data') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.penalty.item_description') }}</th>
                                    <th>{{ __('ui.penalty.item_part_code') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.penalty.item_quantity') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.penalty.item_unit_cost') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.penalty.item_subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penalty->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="ldr-mono">{{ $item->part_code ?: '—' }}</td>
                                        <td class="ldr-table__num">{{ $item->quantity }}</td>
                                        <td class="ldr-table__num">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                        <td class="ldr-table__num">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <dl class="ldr-cost-summary">
                    <div>
                        <dt>{{ __('ui.penalty.repair_subtotal') }}</dt>
                        <dd>Rp {{ number_format($penalty->repair_subtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.subsidy') }} ({{ $penalty->subsidy_percent }}%)</dt>
                        <dd>−Rp {{ number_format($penalty->subsidy_amount, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.admin_fee') }}</dt>
                        <dd>Rp {{ number_format($penalty->admin_fee, 0, ',', '.') }}</dd>
                    </div>
                    <div class="ldr-cost-summary__total">
                        <dt>{{ __('ui.penalty.total') }}</dt>
                        <dd>Rp {{ number_format($penalty->total_amount, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.paid') }}</dt>
                        <dd>Rp {{ number_format($paid, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.remaining') }}</dt>
                        <dd>Rp {{ number_format($remaining, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                @if (! in_array($penalty->status, [\App\Enums\PenaltyStatus::Paid, \App\Enums\PenaltyStatus::Waived], true))
                    <form method="POST" action="{{ route('admin.penalties.items.store', $penalty) }}" class="ldr-parts-form">
                        @csrf
                        <h3>{{ __('ui.penalty.add_item') }}</h3>
                        <div class="ldr-hv-form-grid">
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_description') }}</span>
                                <input class="ldr-input" type="text" name="description" required maxlength="200">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_part_code') }}</span>
                                <input class="ldr-input" type="text" name="part_code" maxlength="60">
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_quantity') }}</span>
                                <input class="ldr-input" type="number" name="quantity" value="1" min="1" max="999" required>
                            </label>
                            <label class="ldr-field">
                                <span>{{ __('ui.penalty.item_unit_cost') }}</span>
                                <input class="ldr-input" type="number" name="unit_cost" value="0" min="0" required>
                            </label>
                        </div>
                        <button class="ldr-btn ldr-btn--secondary" type="submit">{{ __('ui.common.save') }}</button>
                    </form>
                @endif
            </section>

            <section class="ldr-card">
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.penalty.payment_history') }}</h2>
                        <p>{{ __('ui.penalty.verify_payment') }}</p>
                    </div>
                </div>

                @if ($penalty->payments->isEmpty())
                    <p class="ldr-dash-empty">{{ __('ui.penalty.no_payment') }}</p>
                @else
                    <div class="ldr-table-wrap">
                        <table class="ldr-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ui.payment.reference') }}</th>
                                    <th>{{ __('ui.payment.method_label') }}</th>
                                    <th class="ldr-table__num">{{ __('ui.payment.amount') }}</th>
                                    <th>{{ __('ui.payment.status_label') }}</th>
                                    <th>{{ __('ui.payment.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penalty->payments as $payment)
                                    <tr>
                                        <td class="ldr-mono">{{ $payment->reference_number ?: '—' }}</td>
                                        <td>{{ __('ui.payment.method.'.$payment->method->value) }}</td>
                                        <td class="ldr-table__num">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td><x-status-badge type="payment" :status="$payment->status->value" /></td>
                                        <td>{{ $payment->created_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.payments.index', ['search' => $penalty->bap_number]) }}">
                    {{ __('ui.penalty.verify_payment') }}
                </a>
            </section>
        </div>

        <aside class="ldr-handover-side">
            <section class="ldr-card">
                <h2>{{ __('ui.penalty.summary_title') }}</h2>

                <p class="ldr-penalty-total">Rp {{ number_format($penalty->total_amount, 0, ',', '.') }}</p>
                <p class="ldr-muted">{{ __('ui.penalty.remaining') }}: Rp {{ number_format($remaining, 0, ',', '.') }}</p>

                <x-status-badge type="penalty" :status="$penalty->status->value" />

                @if ($penalty->due_at)
                    <p class="ldr-muted">{{ __('ui.penalty.overdue_hint', ['date' => $penalty->due_at->copy()->locale('id')->translatedFormat('d M Y')]) }}</p>
                @endif

                <a class="ldr-btn ldr-btn--secondary ldr-btn--block" href="{{ route('admin.penalties.print', $penalty) }}">
                    <x-icon name="printer" :size="16" />
                    {{ __('ui.penalty.print_bap') }}
                </a>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.maintenance.actions_title') }}</h2>

                <div class="ldr-stack">
                    @if ($penalty->issued_at === null && ! in_array($penalty->status, [\App\Enums\PenaltyStatus::Paid, \App\Enums\PenaltyStatus::Waived], true))
                        <form method="POST" action="{{ route('admin.penalties.issue', $penalty) }}">
                            @csrf
                            <button class="ldr-btn ldr-btn--primary ldr-btn--block" type="submit">
                                <x-icon name="check-circle" :size="16" />
                                {{ __('ui.penalty.issue_action') }}
                            </button>
                        </form>
                    @endif

                    @if ($penalty->escalated_at === null)
                        <form method="POST" action="{{ route('admin.penalties.escalate', $penalty) }}">
                            @csrf
                            <button class="ldr-btn ldr-btn--ghost ldr-btn--block" type="submit">
                                {{ __('ui.penalty.escalate_action') }}
                            </button>
                        </form>
                    @else
                        <p class="ldr-muted">
                            {{ __('ui.penalty.escalate_action') }} ·
                            {{ $penalty->escalated_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                        </p>
                    @endif
                </div>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.penalty.issued_by') }}</h2>
                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.penalty.issued_by') }}</dt>
                        <dd>{{ $penalty->issuer?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.penalty.registered') }}</dt>
                        <dd>{{ $penalty->creator?->name ?? '—' }}</dd>
                    </div>
                </dl>
                <p class="ldr-muted">{{ __('ui.penalty.print_hint') }}</p>
            </section>
        </aside>
    </div>
@endsection
