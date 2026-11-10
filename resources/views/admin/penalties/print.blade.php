@extends(($pdf ?? false) ? 'layouts.pdf' : 'layouts.print')

@section('title', $penalty->bap_number)

@section('content')
    <div class="ldr-print-actions">
        <a class="ldr-btn ldr-btn--ghost" href="{{ route('admin.penalties.show', $penalty) }}">{{ __('ui.common.back') }}</a>
        <button type="button" class="ldr-btn ldr-btn--primary" onclick="window.print()">{{ __('ui.common.print') }}</button>
        <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.penalties.print', $penalty) }}?format=pdf">{{ __('ui.common.download_pdf') }}</a>
    </div>

    <article class="ldr-print-sheet">
        <header class="ldr-print-head">
            <div>
                <span class="ldr-eyebrow">Lendora</span>
                <h1>{{ __('ui.penalty.bap_title') }}</h1>
                <p class="ldr-mono">{{ $penalty->bap_number }}</p>
            </div>
            <div class="ldr-print-head__meta">
                <span>{{ __('ui.penalty.status_label') }}</span>
                <strong>{{ __('ui.penalty.status.'.$penalty->status->value) }}</strong>
                <span>{{ __('ui.penalty.issued_at') }}</span>
                <strong>{{ $penalty->issued_at?->copy()->locale('id')->translatedFormat('d M Y') ?? __('ui.penalty.not_issued') }}</strong>
            </div>
        </header>

        <section class="ldr-print-section">
            <h2>{{ __('ui.penalty.borrower_data') }}</h2>
            <dl class="ldr-print-meta">
                <div>
                    <dt>{{ __('ui.penalty.borrower_data') }}</dt>
                    <dd>{{ $penalty->borrower?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>NIM</dt>
                    <dd>{{ $penalty->borrower?->nim ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.booking.detail') }}</dt>
                    <dd class="ldr-mono">{{ $penalty->booking?->code ?: '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('ui.inspection.linked_maintenance') }}</dt>
                    <dd class="ldr-mono">{{ $penalty->maintenance?->code ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="ldr-print-section">
            <h2>{{ __('ui.penalty.damage_finding') }}</h2>
            <dl class="ldr-print-meta">
                <div>
                    <dt>{{ __('ui.nav.assets') }}</dt>
                    <dd>{{ $penalty->asset?->code ?: '—' }} · {{ $penalty->asset?->name ?: '—' }}</dd>
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
                    <dt>{{ __('ui.penalty.case_category') }}</dt>
                    <dd>{{ $penalty->case_category ?: '—' }}</dd>
                </div>
            </dl>
            <p class="ldr-print-text"><strong>{{ __('ui.penalty.damage_desc') }}:</strong> {{ $penalty->damage_description }}</p>
            @if ($penalty->chronology)
                <p class="ldr-print-text"><strong>{{ __('ui.penalty.chronology') }}:</strong> {{ $penalty->chronology }}</p>
            @endif
        </section>

        <section class="ldr-print-section">
            <h2>{{ __('ui.penalty.breakdown') }}</h2>
            <table class="ldr-print-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.penalty.item_description') }}</th>
                        <th>{{ __('ui.penalty.item_part_code') }}</th>
                        <th class="ldr-print-num">{{ __('ui.penalty.item_quantity') }}</th>
                        <th class="ldr-print-num">{{ __('ui.penalty.item_unit_cost') }}</th>
                        <th class="ldr-print-num">{{ __('ui.penalty.item_subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penalty->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->part_code ?: '—' }}</td>
                            <td class="ldr-print-num">{{ $item->quantity }}</td>
                            <td class="ldr-print-num">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="ldr-print-num">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <dl class="ldr-print-totals">
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
                <div class="ldr-print-totals__grand">
                    <dt>{{ __('ui.penalty.total') }}</dt>
                    <dd>Rp {{ number_format($penalty->total_amount, 0, ',', '.') }}</dd>
                </div>
            </dl>

            <p class="ldr-print-text">
                {{ __('ui.penalty.due') }}:
                {{ $penalty->due_at?->copy()->locale('id')->translatedFormat('d M Y') ?: '—' }}
            </p>
        </section>

        @if ($penalty->notes)
            <section class="ldr-print-section">
                <h2>{{ __('ui.booking.notes') }}</h2>
                <p class="ldr-print-text">{{ $penalty->notes }}</p>
            </section>
        @endif

        <section class="ldr-print-signatures">
            <div>
                <span>{{ __('ui.penalty.borrower_data') }}</span>
                <strong>{{ $penalty->borrower?->name ?? '—' }}</strong>
                <span class="ldr-print-signature-line"></span>
            </div>
            <div>
                <span>{{ __('ui.penalty.issued_by') }}</span>
                <strong>{{ $penalty->issuer?->name ?: '—' }}</strong>
                <span class="ldr-print-signature-line"></span>
            </div>
        </section>

        <footer class="ldr-print-footer">
            <span>{{ $penalty->bap_number }}</span>
            <span>{{ __('ui.penalty.print_hint') }}</span>
        </footer>
    </article>
@endsection
