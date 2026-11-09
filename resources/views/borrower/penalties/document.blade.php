@extends('layouts.borrower')

@section('title', $penalty->bap_number)
@section('nav', 'profile')

@section('content')
    <div class="ldr-print-page" id="print-area">
        <header class="ldr-print-header">
            <h1>{{ __('ui.penalty.bap_title') }}</h1>
            <p class="ldr-print-meta">{{ $penalty->bap_number }} · {{ $penalty->incident_at->format('d M Y') }}</p>
        </header>

        <div class="ldr-print-body">
            <div class="ldr-print-section">
                <h3>{{ __('ui.penalty.asset') }}</h3>
                <p>{{ $penalty->asset->code ?? '—' }} — {{ $penalty->asset->name ?? '—' }}</p>
            </div>

            <div class="ldr-print-section">
                <h3>{{ __('ui.penalty.damage') }}</h3>
                <p>{{ $penalty->damage_description ?? '—' }}</p>
            </div>

            <div class="ldr-print-section">
                <h3>{{ __('ui.penalty.breakdown') }}</h3>
                <table class="ldr-print-table">
                    <tr>
                        <td>{{ __('ui.penalty.repair_subtotal') }}</td>
                        <td>Rp {{ number_format($breakdown['repair_subtotal'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('ui.penalty.subsidy') }}</td>
                        <td>−Rp {{ number_format($breakdown['subsidy_amount'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('ui.penalty.admin_fee') }}</td>
                        <td>Rp {{ number_format($breakdown['admin_fee'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="ldr-print-total">
                        <td>{{ __('ui.penalty.total') }}</td>
                        <td>Rp {{ number_format($breakdown['total_amount'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <footer class="ldr-print-footer">
            <p>{{ __('ui.penalty.issued_by') }}: {{ $penalty->inspector_name ?? '—' }}</p>
            <p>{{ __('ui.penalty.status_label') }}: {{ __('ui.penalty.status.' . $penalty->status->value) }}</p>
        </footer>
    </div>

    <script>
        window.print();
    </script>
@endsection