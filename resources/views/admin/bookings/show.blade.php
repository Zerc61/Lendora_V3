@extends('layouts.admin')

@section('title', $booking->code)

@php
    $roleSlug = auth()->user()->role->slug;
    $canReject = $canDecide && in_array($roleSlug, ['operator', 'organization_admin'], true);
    $isPending = $booking->status === App\Enums\BookingStatus::Pending;

    $formatRange = $booking->requested_start_date->format('M Y') === $booking->requested_end_date->format('M Y')
        ? $booking->requested_start_date->copy()->locale('id')->translatedFormat('d').'–'.$booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y')
        : $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y').'–'.$booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y');
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.bookings.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.booking.back_to_queue') }}
    </a>

    <div class="ldr-page-head">
        <div>
            <div class="ldr-booking-card__head">
                <span class="ldr-mono ldr-booking-detail__code">{{ $booking->code }}</span>
                <x-status-badge type="booking" :status="$booking->status->value" />
            </div>
            <p class="ldr-muted">{{ $formatRange }} · {{ $booking->purpose }}</p>
        </div>
    </div>

    @if ($booking->status === App\Enums\BookingStatus::Rejected && $booking->rejection_reason)
        <div class="ldr-alert ldr-alert--danger" role="alert">
            <strong>{{ __('ui.booking.reason') }}:</strong>
            {{ $booking->rejection_reason }}
        </div>
    @endif

    @if ($isPending && ! $canDecide && $blockReason)
        <div class="ldr-alert ldr-alert--warning" role="status">
            {{ $blockReason }}
        </div>
    @endif

    <div class="ldr-detail-grid">
        <div class="ldr-detail-grid__main">
            <section class="ldr-card">
                <h2>{{ __('ui.booking.borrower') }}</h2>

                <dl class="ldr-spec-list">
                    <div>
                        <dt>{{ __('ui.booking.borrower') }}</dt>
                        <dd>{{ $booking->borrower->name }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.auth.email') }}</dt>
                        <dd>{{ $booking->borrower->email }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.purpose') }}</dt>
                        <dd>{{ $booking->purpose }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.notes') }}</dt>
                        <dd>{{ $booking->notes ?: '—' }}</dd>
                    </div>
                    @if ($booking->approved_by)
                        <div>
                            <dt>{{ __('ui.booking.approved_by') }}</dt>
                            <dd>{{ $booking->approvedBy?->name ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.booking.assets') }}</h2>

                <div class="ldr-table-wrap">
                    <table class="ldr-table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.booking.assets') }}</th>
                                <th>{{ __('ui.booking.item_condition_out') }}</th>
                                <th>{{ __('ui.booking.item_condition_in') }}</th>
                                <th>{{ __('ui.booking.filter_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($booking->items as $item)
                                <tr>
                                    <td>
                                        <span class="ldr-table__name">{{ $item->asset->name }}</span>
                                        <span class="ldr-table__sub ldr-mono">{{ $item->asset->code }}</span>
                                    </td>
                                    <td class="ldr-muted">{{ $item->condition_out ?: '—' }}</td>
                                    <td class="ldr-muted">{{ $item->condition_in ?: '—' }}</td>
                                    <td><x-status-badge type="item" :status="$item->status->value" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <dl class="ldr-spec-list ldr-spec-list--inline">
                    <div>
                        <dt>{{ __('ui.booking.period') }}</dt>
                        <dd>{{ $formatRange }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.booking.due_at') }}</dt>
                        <dd>{{ $booking->due_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <aside class="ldr-detail-grid__side">
            <section class="ldr-card">
                <h2>{{ __('ui.booking.timeline') }}</h2>

                <x-timeline :timeline="$timeline" :status="$booking->status->value" />
            </section>

            <section class="ldr-card">
                <h2>{{ __('ui.booking.handover_history') }}</h2>
                <p class="ldr-muted">{{ __('ui.booking.handover_empty') }}</p>
            </section>

            @if ($isPending)
                <section class="ldr-card ldr-decision-card">
                    <h2>{{ __('ui.booking.decision') }}</h2>

                    <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                        @csrf
                        <button type="submit" class="ldr-btn ldr-btn--primary ldr-btn--block" @disabled(! $canDecide)>
                            {{ __('ui.booking.approve') }}
                        </button>
                    </form>

                    <button type="button"
                            class="ldr-btn ldr-btn--danger ldr-btn--block"
                            data-modal-open="reject-booking-{{ $booking->id }}"
                            @disabled(! $canReject)>
                        {{ __('ui.booking.reject') }}
                    </button>

                    @if (! $canDecide && $blockReason)
                        <p class="ldr-form-hint">{{ $blockReason }}</p>
                    @endif
                </section>
            @endif
        </aside>
    </div>

    @if ($isPending && $canReject)
        <x-modal :id="'reject-booking-'.$booking->id" :title="__('ui.booking.reject_title')">
            <p class="ldr-modal__message">{{ $booking->code }} · {{ $booking->borrower->name }}</p>

            <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="ldr-reject-form">
                @csrf

                <div class="ldr-field">
                    <label class="ldr-label" for="rejection-reason-{{ $booking->id }}">
                        {{ __('ui.booking.reason') }} <span class="ldr-label__required" aria-hidden="true">*</span>
                    </label>
                    <textarea id="rejection-reason-{{ $booking->id }}"
                              class="ldr-input"
                              name="rejection_reason"
                              minlength="5"
                              maxlength="255"
                              required
                              placeholder="{{ __('ui.booking.reject_placeholder') }}">{{ old('rejection_reason') }}</textarea>
                    <p class="ldr-form-hint">{{ __('ui.booking.reject_help') }}</p>
                </div>

                <div class="ldr-modal__actions">
                    <button type="button" class="ldr-btn ldr-btn--secondary" data-modal-close>
                        {{ __('ui.common.cancel') }}
                    </button>
                    <button type="submit" class="ldr-btn ldr-btn--danger">
                        {{ __('ui.booking.reject_confirm') }}
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection
