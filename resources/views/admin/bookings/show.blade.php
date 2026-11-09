@extends('layouts.admin')

@section('title', $booking->code)

@php
    $roleSlug = auth()->user()->role->slug;
    $canReject = $canDecide && in_array($roleSlug, ['operator', 'organization_admin'], true);
    $isPending = $booking->status === App\Enums\BookingStatus::Pending;
    $isPickup = $booking->status === App\Enums\BookingStatus::Approved;
    $canHandover = in_array($roleSlug, ['operator', 'organization_admin'], true)
        && in_array($booking->status, [App\Enums\BookingStatus::Approved, App\Enums\BookingStatus::Active], true);

    $formatRange = $booking->requested_start_date->format('M Y') === $booking->requested_end_date->format('M Y')
        ? $booking->requested_start_date->copy()->locale('id')->translatedFormat('d').'–'.$booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y')
        : $booking->requested_start_date->copy()->locale('id')->translatedFormat('d M Y').'–'.$booking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y');

    $borrower = $booking->borrower;
@endphp

@section('content')
    <a class="ldr-back-link" href="{{ route('admin.bookings.index') }}">
        <x-icon name="chevron-left" :size="18" />
        {{ __('ui.booking.back_to_queue') }}
    </a>

    <section class="ldr-booking-hero">
        <div class="ldr-booking-hero__main">
            <div class="ldr-booking-hero__tags">
                <span class="ldr-mono ldr-booking-hero__code">{{ $booking->code }}</span>
                <x-status-badge type="booking" :status="$booking->status->value" />
            </div>

            <h1>{{ $borrower->name }}</h1>

            <p class="ldr-booking-hero__meta">
                @if ($borrower->nim)
                    NIM {{ $borrower->nim }}
                @endif
                @if ($borrower->study_program)
                    · {{ $borrower->study_program }}
                @endif
                @if ($borrower->semester)
                    · Semester {{ $borrower->semester }}
                @endif
            </p>

            <div class="ldr-booking-hero__facts">
                <span>
                    <x-icon name="calendar" :size="15" />
                    {{ $formatRange }}
                </span>
                <span>
                    <x-icon name="package" :size="15" />
                    {{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}
                </span>
                <span>
                    <x-icon name="clipboard-list" :size="15" />
                    {{ $booking->purpose }}
                </span>
            </div>
        </div>

        @if ($isPending || $canHandover)
            <div class="ldr-booking-hero__actions">
                @if ($isPending)
                    <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                        @csrf
                        <button type="submit" class="ldr-btn ldr-btn--primary" @disabled(! $canDecide)>
                            <x-icon name="check-circle" :size="18" />
                            {{ __('ui.booking.approve') }}
                        </button>
                    </form>

                    <button type="button"
                            class="ldr-btn ldr-btn--secondary"
                            data-modal-open="reject-booking-{{ $booking->id }}"
                            @disabled(! $canReject)>
                        <x-icon name="x" :size="18" />
                        {{ __('ui.booking.reject') }}
                    </button>
                @endif

                @if ($canHandover)
                    <a class="ldr-btn ldr-btn--primary"
                       href="{{ route('admin.bookings.handover', ['booking' => $booking, 'type' => $isPickup ? 'pickup' : 'return']) }}">
                        <x-icon name="arrow-left-right" :size="18" />
                        {{ $isPickup ? __('ui.handover.pickup_action') : __('ui.handover.return_action') }}
                    </a>
                @endif
            </div>
        @endif
    </section>

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

                <dl class="ldr-spec-list ldr-spec-list--two">
                    <div>
                        <dt>{{ __('ui.booking.borrower') }}</dt>
                        <dd>{{ $borrower->name }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('ui.auth.email') }}</dt>
                        <dd>{{ $borrower->email }}</dd>
                    </div>
                    @if ($borrower->nim)
                        <div>
                            <dt>{{ __('ui.profile.nim') }}</dt>
                            <dd>{{ $borrower->nim }}</dd>
                        </div>
                    @endif
                    @if ($borrower->study_program)
                        <div>
                            <dt>{{ __('ui.profile.study_program') }}</dt>
                            <dd>{{ $borrower->study_program }}</dd>
                        </div>
                    @endif
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
                <div class="ldr-card-head ldr-card-head--flush">
                    <div>
                        <h2>{{ __('ui.booking.assets') }}</h2>
                        <p>{{ __('ui.booking.items_readonly') }}</p>
                    </div>
                    <span class="ldr-queue-card__count">{{ __('ui.booking.items_count', ['count' => $booking->items->count()]) }}</span>
                </div>

                <div class="ldr-unit-list">
                    @forelse ($booking->items as $item)
                        @php
                            $asset = $item->asset;
                            $photo = $asset?->photos->first();
                        @endphp
                        <article class="ldr-unit">
                            <span class="ldr-unit__media">
                                @if ($photo)
                                    <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                         alt="{{ $asset->name }}"
                                         loading="lazy"
                                         width="72"
                                         height="72">
                                @else
                                    <x-icon name="camera" :size="24" />
                                @endif
                            </span>

                            <div class="ldr-unit__body">
                                <span class="ldr-mono">{{ $asset?->code }}</span>
                                <strong>{{ $asset?->name ?? '—' }}</strong>
                                <span class="ldr-muted">
                                    {{ collect([$asset?->brand, $asset?->model])->filter()->join(' · ') ?: '—' }}
                                </span>
                            </div>

                            <dl class="ldr-unit__facts">
                                <div>
                                    <dt>{{ __('ui.booking.item_condition_out') }}</dt>
                                    <dd>{{ $item->condition_out ? __('ui.condition.'.$item->condition_out) : '—' }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('ui.booking.item_condition_in') }}</dt>
                                    <dd>{{ $item->condition_in ? __('ui.condition.'.$item->condition_in) : '—' }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('ui.booking.filter_status') }}</dt>
                                    <dd><x-status-badge type="item" :status="$item->status->value" /></dd>
                                </div>
                            </dl>

                            <span class="ldr-unit__value">
                                Rp {{ number_format($asset?->purchase_price ?? 0, 0, ',', '.') }}
                            </span>
                        </article>
                    @empty
                        <p class="ldr-muted">{{ __('ui.booking.no_items') }}</p>
                    @endforelse
                </div>

                <div class="ldr-booking-totals">
                    <div>
                        <span class="ldr-muted">{{ __('ui.booking.period') }}</span>
                        <strong>{{ $formatRange }}</strong>
                    </div>
                    <div>
                        <span class="ldr-muted">{{ __('ui.booking.due_at') }}</span>
                        <strong>{{ $booking->due_at?->copy()->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="ldr-muted">{{ __('ui.queue.total_value') }}</span>
                        <strong>Rp {{ number_format($booking->totalAssetValue(), 0, ',', '.') }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <aside class="ldr-detail-grid__side">
            <section class="ldr-card">
                <h2>{{ __('ui.booking.timeline') }}</h2>

                <x-timeline :timeline="$timeline" :status="$booking->status->value" />
            </section>

            @php
                $pendingExtensions = $booking->extensions->where('status', \App\Enums\BookingExtensionStatus::Pending);
            @endphp

            @if ($pendingExtensions->isNotEmpty())
                <section class="ldr-card">
                    <h2>{{ __('ui.booking.extension') }}</h2>

                    @foreach ($pendingExtensions as $extension)
                        <dl class="ldr-spec-list">
                            <div>
                                <dt>{{ __('ui.booking.due_at') }}</dt>
                                <dd>{{ $extension->requested_due_at->translatedFormat('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt>{{ __('ui.booking.extension_reason') }}</dt>
                                <dd>{{ $extension->reason }}</dd>
                            </div>
                        </dl>

                        <form method="POST" action="{{ route('admin.bookings.extensions.approve', [$booking, $extension]) }}">
                            @csrf
                            <button type="submit" class="ldr-btn ldr-btn--sm ldr-btn--primary">
                                {{ __('ui.booking.approve') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.bookings.extensions.reject', [$booking, $extension]) }}" class="ldr-field">
                            @csrf
                            <label class="ldr-label" for="extension-reason-{{ $extension->id }}">{{ __('ui.booking.reason') }}</label>
                            <input id="extension-reason-{{ $extension->id }}" name="rejection_reason" class="ldr-input" required minlength="5" maxlength="255">
                            @error('rejection_reason') <p class="ldr-field__error">{{ $message }}</p> @enderror
                            <button type="submit" class="ldr-btn ldr-btn--sm ldr-btn--danger">
                                {{ __('ui.booking.reject') }}
                            </button>
                        </form>
                    @endforeach
                </section>
            @endif

            <section class="ldr-card">
                <h2>{{ __('ui.booking.handover_history') }}</h2>

                @forelse ($booking->handovers as $handover)
                    <div class="ldr-spec-list">
                        <div>
                            <dt>{{ $handover->type->label() }}</dt>
                            <dd>
                                {{ $handover->happened_at?->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                                · {{ $handover->performedBy?->name ?? '—' }}
                            </dd>
                        </div>
                        @if ($handover->notes)
                            <div>
                                <dt>{{ __('ui.booking.notes') }}</dt>
                                <dd>{{ $handover->notes }}</dd>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="ldr-muted">{{ __('ui.booking.handover_empty') }}</p>
                @endforelse
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
            <p class="ldr-modal__message">{{ $booking->code }} · {{ $borrower->name }}</p>

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
