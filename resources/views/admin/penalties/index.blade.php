@extends('layouts.admin')

@section('title', 'BAP & Denda')

@section('content')
    <div class="ldr-page-head">
        <div>
            <span class="ldr-eyebrow">O3 · BAP &amp; Denda</span>
            <h1>BAP &amp; Denda</h1>
            <p class="ldr-muted">{{ __('ui.penalty.index_description') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--primary" href="{{ route('admin.penalties.create') }}">
                <x-icon name="plus" :size="18" />
                {{ __('ui.penalty.create_title') }}
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
    @endif

    <div class="ldr-kpi-grid">
        <div class="ldr-kpi">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.penalty.total_bap') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="clipboard-list" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['total'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.penalty.due') }} &amp; {{ __('ui.penalty.status.paid') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--warning">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.penalty.waiting_count') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bell" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ $summary['waiting'] }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.penalty.remaining') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--danger">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.penalty.outstanding') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="bar-chart" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.penalty.total') }}</span>
        </div>

        <div class="ldr-kpi ldr-kpi--success">
            <div class="ldr-kpi__head">
                <span class="ldr-kpi__label">{{ __('ui.penalty.paid') }}</span>
                <span class="ldr-kpi__icon"><x-icon name="check-circle" :size="18" /></span>
            </div>
            <span class="ldr-kpi__value">{{ max(0, $summary['total'] - $summary['waiting']) }}</span>
            <span class="ldr-kpi__hint">{{ __('ui.penalty.paid') }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.penalties.index') }}" class="ldr-filter-bar" role="search">
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="penalty-search">{{ __('ui.penalty.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="penalty-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.penalty.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="penalty-status">{{ __('ui.penalty.status_label') }}</label>
            <select id="penalty-status" class="ldr-input" name="status">
                <option value="">{{ __('ui.common.all') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ __('ui.penalty.status.'.$status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__actions">
            <button class="ldr-btn ldr-btn--secondary ldr-btn--sm" type="submit">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.penalties.index') }}">{{ __('ui.common.reset') }}</a>
        </div>
    </form>

    @if ($penalties->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            :title="__('ui.penalty.empty_title')"
            :description="__('ui.penalty.empty_description')"
        />
    @else
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.penalty.bap_title') }}</th>
                            <th>{{ __('ui.penalty.borrower_data') }}</th>
                            <th>{{ __('ui.nav.assets') }}</th>
                            <th class="ldr-table__num">{{ __('ui.penalty.total') }}</th>
                            <th class="ldr-table__num">{{ __('ui.penalty.remaining') }}</th>
                            <th>{{ __('ui.penalty.status_label') }}</th>
                            <th>{{ __('ui.penalty.due') }}</th>
                            <th class="ldr-table__actions-head">{{ __('ui.common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penalties as $penalty)
                            <tr>
                                <td>
                                    <a class="ldr-table__code ldr-mono" href="{{ route('admin.penalties.show', $penalty) }}">
                                        {{ $penalty->bap_number }}
                                    </a>
                                    <span class="ldr-table__sub">
                                        {{ $penalty->incident_at?->copy()->locale('id')->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $penalty->borrower?->name ?? '—' }}</span>
                                    <span class="ldr-table__sub ldr-mono">{{ $penalty->borrower?->nim ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $penalty->asset?->name ?? '—' }}</span>
                                    <span class="ldr-table__sub ldr-table__code">{{ $penalty->asset?->code ?: '—' }}</span>
                                </td>
                                <td class="ldr-table__num">Rp {{ number_format($penalty->total_amount, 0, ',', '.') }}</td>
                                <td class="ldr-table__num">Rp {{ number_format($penalty->remainingAmount(), 0, ',', '.') }}</td>
                                <td><x-status-badge type="penalty" :status="$penalty->status->value" /></td>
                                <td class="ldr-table__num">
                                    {{ $penalty->due_at?->copy()->locale('id')->translatedFormat('d M Y') ?: '—' }}
                                </td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
                                        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.penalties.show', $penalty) }}">
                                            {{ __('ui.inspection.detail') }}
                                        </a>
                                        <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.penalties.print', $penalty) }}">
                                            {{ __('ui.common.print') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$penalties" />
        </div>
    @endif
@endsection
