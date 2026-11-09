@extends('layouts.borrower')

@section('title', __('ui.penalty.penalties'))
@section('nav', 'profile')

@section('content')
    <div class="ldr-penalties-page">
        <div class="ldr-page-head">
            <div>
                <h1>{{ __('ui.penalty.penalties') }}</h1>
                <p class="ldr-muted">{{ __('ui.penalty.subtitle') }}</p>
            </div>
        </div>

        @if ($summary['total_penalties'] > 0)
            <div class="ldr-penalty-summary">
                <div class="ldr-penalty-summary__item">
                    <span class="ldr-penalty-summary__label">{{ __('ui.penalty.total') }}</span>
                    <span class="ldr-penalty-summary__value">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="ldr-penalty-summary__item">
                    <span class="ldr-penalty-summary__label">{{ __('ui.penalty.paid') }}</span>
                    <span class="ldr-penalty-summary__value">Rp {{ number_format($summary['paid_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="ldr-penalty-summary__item">
                    <span class="ldr-penalty-summary__label">{{ __('ui.penalty.remaining') }}</span>
                    <span class="ldr-penalty-summary__value ldr-penalty-summary__value--danger">Rp {{ number_format($summary['remaining_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        @endif

        @if ($penalties->isEmpty())
            <x-empty-state
                icon="alert-triangle"
                :title="__('ui.penalty.empty_title')"
                :description="__('ui.penalty.empty_description')"
            />
        @else
            <div class="ldr-penalty-list">
                @foreach ($penalties as $penalty)
                    <article class="ldr-penalty-card">
                        <div class="ldr-penalty-card__head">
                            <span class="ldr-penalty-card__bap">{{ $penalty->bap_number }}</span>
                            <x-status-badge :status="$penalty->status->value" class="ldr-penalty-card__status" type="penalty" />
                        </div>

                        <div class="ldr-penalty-card__body">
                            <div class="ldr-penalty-card__asset">
                                <span class="ldr-penalty-card__code">{{ $penalty->asset->code ?? '—' }}</span>
                                <span class="ldr-penalty-card__name">{{ $penalty->asset->name ?? '—' }}</span>
                            </div>

                            <div class="ldr-penalty-card__meta">
                                <span>{{ __('ui.penalty.damage_desc') }}: {{ $penalty->damage_description ?? '—' }}</span>
                                <span>{{ __('ui.penalty.incident_date') }}: {{ $penalty->incident_at->format('d M Y') }}</span>
                            </div>

                            <div class="ldr-penalty-card__amount">
                                <span class="ldr-penalty-card__total">Rp {{ number_format($penalty->total_amount, 0, ',', '.') }}</span>
                                <a href="{{ route('borrower.penalties.show', $penalty) }}" class="ldr-btn ldr-btn--sm ldr-btn--primary">
                                    {{ __('ui.common.action') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$penalties" />
        @endif
    </div>
@endsection