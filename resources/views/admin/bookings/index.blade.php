@extends('layouts.admin')

@section('title', __('ui.booking.queue'))

@php
    $roleSlug = auth()->user()->role->slug;
    $canRejectRole = in_array($roleSlug, ['operator', 'organization_admin'], true);

    $formatRange = function ($booking): string {
        $start = $booking->requested_start_date;
        $end = $booking->requested_end_date;

        if ($start->format('M Y') === $end->format('M Y')) {
            return $start->copy()->locale('id')->translatedFormat('d').'–'.$end->copy()->locale('id')->translatedFormat('d M Y');
        }

        return $start->copy()->locale('id')->translatedFormat('d M Y').'–'.$end->copy()->locale('id')->translatedFormat('d M Y');
    };

    $activeTab = $filters['status'] ?? null;

    $queueKpis = [
        ['key' => 'pending', 'icon' => 'clipboard-list', 'tone' => 'warning', 'label' => __('ui.booking_status.pending'), 'hint' => __('ui.queue.kpi_pending_hint')],
        ['key' => 'approved', 'icon' => 'check-circle', 'tone' => 'info', 'label' => __('ui.booking_status.approved'), 'hint' => __('ui.queue.kpi_approved_hint')],
        ['key' => 'active', 'icon' => 'layers', 'tone' => 'success', 'label' => __('ui.booking_status.active'), 'hint' => __('ui.queue.kpi_active_hint')],
        ['key' => 'returned', 'icon' => 'arrow-left-right', 'tone' => 'neutral', 'label' => __('ui.booking_status.returned'), 'hint' => __('ui.queue.kpi_returned_hint')],
    ];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.booking.queue') }}</h1>
            <p class="ldr-muted">{{ __('ui.queue.subtitle') }}</p>
        </div>

        <div class="ldr-page-head__actions">
            @if (in_array(auth()->user()->role->slug, ['operator', 'organization_admin'], true))
                <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.calendar') }}">
                    <x-icon name="calendar" :size="18" />
                    {{ __('ui.booking.calendar') }}
                </a>
            @endif
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.index', ['status' => 'all']) }}">
                {{ __('ui.queue.history') }}
                <x-icon name="arrow-right" :size="16" />
            </a>
        </div>
    </div>

    <div class="ldr-kpi-grid">
        @foreach ($queueKpis as $kpi)
            <div class="ldr-kpi ldr-kpi--{{ $kpi['tone'] }}">
                <div class="ldr-kpi__head">
                    <span class="ldr-kpi__label">{{ $kpi['label'] }}</span>
                    <span class="ldr-kpi__icon">
                        <x-icon :name="$kpi['icon']" :size="18" />
                    </span>
                </div>
                <span class="ldr-kpi__value">{{ $queueStats[$kpi['key']] }}</span>
                <span class="ldr-kpi__hint">{{ $kpi['hint'] }}</span>
            </div>
        @endforeach
    </div>

    <x-status-tabs
        route="admin.bookings.index"
        :current="$activeTab"
        :counts="$counts"
        :query="['search' => $filters['search'] ?? null, 'date_from' => $filters['date_from'] ?? null, 'date_to' => $filters['date_to'] ?? null]"
    />

    <form method="GET" action="{{ route('admin.bookings.index') }}" class="ldr-filter-bar" role="search" data-auto-submit>
        <div class="ldr-filter-bar__search">
            <label class="ldr-sr-only" for="booking-search">{{ __('ui.booking.filter_search') }}</label>
            <x-icon name="search" :size="18" class="ldr-filter-bar__search-icon" />
            <input id="booking-search"
                   class="ldr-input"
                   type="search"
                   name="search"
                   value="{{ $filters['search'] ?? '' }}"
                   placeholder="{{ __('ui.booking.filter_search') }}">
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="booking-status">{{ __('ui.booking.filter_status') }}</label>
            <select id="booking-status" class="ldr-input" name="status" data-auto-submit-field>
                <option value="all">{{ __('ui.common.all') }}</option>
                @foreach (App\Enums\BookingStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>
                        {{ __('ui.booking_status.'.$status->value) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="booking-date-from">{{ __('ui.booking.filter_date_from') }}</label>
            <input id="booking-date-from"
                   class="ldr-input"
                   type="date"
                   name="date_from"
                   value="{{ $filters['date_from'] ?? '' }}"
                   aria-label="{{ __('ui.booking.filter_date_from') }}"
                   data-auto-submit-field>
        </div>

        <div class="ldr-filter-bar__select">
            <label class="ldr-sr-only" for="booking-date-to">{{ __('ui.booking.filter_date_to') }}</label>
            <input id="booking-date-to"
                   class="ldr-input"
                   type="date"
                   name="date_to"
                   value="{{ $filters['date_to'] ?? '' }}"
                   aria-label="{{ __('ui.booking.filter_date_to') }}"
                   data-auto-submit-field>
        </div>

        <div class="ldr-filter-bar__actions">
            <button type="submit" class="ldr-btn ldr-btn--secondary ldr-btn--sm">{{ __('ui.common.apply') }}</button>
            <a class="ldr-btn ldr-btn--ghost ldr-btn--sm" href="{{ route('admin.bookings.index', ['status' => 'all']) }}">
                {{ __('ui.common.reset') }}
            </a>
        </div>
    </form>

    @if ($bookings->isEmpty())
        <x-empty-state
            icon="clipboard-list"
            :title="__('ui.booking.empty')"
            :description="__('ui.booking.empty_tab')"
        />
    @else
        <div class="ldr-queue">
            @foreach ($bookings as $booking)
                @php
                    $conflict = $conflicts[$booking->id] ?? null;
                    $decidable = $canDecide[$booking->id] ?? false;
                    $blockReason = $limitExceeded[$booking->id] ?? null;
                    $rejectable = $decidable && $canRejectRole && $booking->status === App\Enums\BookingStatus::Pending;
                    $itemCount = $booking->items->count();
                @endphp

                <article class="ldr-queue-card">
                    <header class="ldr-queue-card__head">
                        <div class="ldr-queue-card__id">
                            <a class="ldr-mono" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                            <x-status-badge type="booking" :status="$booking->status->value" />
                        </div>

                        <span class="ldr-queue-card__submitted">
                            <x-icon name="calendar" :size="14" />
                            {{ $booking->created_at->copy()->locale('id')->translatedFormat('d M Y H:i') }}
                        </span>
                    </header>

                    <div class="ldr-queue-card__body">
                        <div class="ldr-queue-person">
                            <span class="ldr-queue-person__avatar" aria-hidden="true">
                                {{ mb_substr($booking->borrower->name, 0, 1) }}
                            </span>
                            <div>
                                <strong>{{ $booking->borrower->name }}</strong>
                                <span class="ldr-queue-person__meta">
                                    @if ($booking->borrower->nim)
                                        {{ $booking->borrower->nim }}
                                    @endif
                                    @if ($booking->borrower->study_program)
                                        · {{ $booking->borrower->study_program }}
                                    @endif
                                </span>
                                <p class="ldr-queue-person__purpose">{{ $booking->purpose }}</p>
                            </div>
                        </div>

                        <div class="ldr-queue-period">
                            <span class="ldr-queue-period__label">{{ __('ui.booking.period') }}</span>
                            <strong>{{ $formatRange($booking) }}</strong>
                            <span class="ldr-muted">{{ __('ui.booking.duration_days', ['count' => $booking->durationInDays()]) }}</span>
                        </div>
                    </div>

                    <div class="ldr-queue-items">
                        @foreach ($booking->items as $item)
                            @php
                                $photo = $item->asset?->photos->first();
                            @endphp
                            <a class="ldr-queue-item" href="{{ route('admin.bookings.show', $booking) }}">
                                <span class="ldr-queue-item__media">
                                    @if ($photo)
                                        <img src="{{ Illuminate\Support\Facades\Storage::url($photo->path) }}"
                                             alt="{{ $item->asset->name }}"
                                             loading="lazy"
                                             width="44"
                                             height="44">
                                    @else
                                        <x-icon name="camera" :size="18" />
                                    @endif
                                </span>
                                <span class="ldr-queue-item__body">
                                    <strong>{{ $item->asset?->name ?? '—' }}</strong>
                                    <span class="ldr-mono">{{ $item->asset?->code }}</span>
                                </span>
                                <span class="ldr-queue-item__value">
                                    Rp {{ number_format($item->asset?->purchase_price ?? 0, 0, ',', '.') }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    @if ($conflict)
                        <div class="ldr-queue-card__alert">
                            <x-conflict-banner :message="$conflict" />
                        </div>
                    @endif

                    <footer class="ldr-queue-card__foot">
                        <div class="ldr-queue-card__total">
                            <span class="ldr-muted">{{ __('ui.queue.total_value') }}</span>
                            <strong>Rp {{ number_format($booking->totalAssetValue(), 0, ',', '.') }}</strong>
                            <span class="ldr-queue-card__count">{{ __('ui.booking.items_count', ['count' => $itemCount]) }}</span>
                        </div>

                        <div class="ldr-queue-card__actions">
                            <a class="ldr-btn ldr-btn--secondary ldr-btn--sm" href="{{ route('admin.bookings.show', $booking) }}">
                                {{ __('ui.booking.detail_action') }}
                            </a>

                            @if ($booking->status === App\Enums\BookingStatus::Pending)
                                <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                    @csrf
                                    <button type="submit"
                                            class="ldr-btn ldr-btn--primary ldr-btn--sm"
                                            @disabled(! $decidable)>
                                        {{ __('ui.booking.approve') }}
                                    </button>
                                </form>

                                <button type="button"
                                        class="ldr-btn ldr-btn--danger ldr-btn--sm"
                                        data-modal-open="reject-booking-{{ $booking->id }}"
                                        @disabled(! $rejectable)>
                                    {{ __('ui.booking.reject') }}
                                </button>
                            @endif
                        </div>
                    </footer>

                    @if ($booking->status === App\Enums\BookingStatus::Pending && ! $decidable && $blockReason)
                        <p class="ldr-queue-card__note">
                            <x-icon name="alert-circle" :size="15" />
                            {{ $blockReason }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <x-pagination :paginator="$bookings" />
    @endif

    @foreach ($bookings as $booking)
        @if ($booking->status === App\Enums\BookingStatus::Pending && ($canDecide[$booking->id] ?? false) && $canRejectRole)
            <x-modal :id="'reject-booking-'.$booking->id" :title="__('ui.booking.reject_title')">
                <p class="ldr-modal__message">
                    {{ $booking->code }} · {{ $booking->borrower->name }}
                </p>

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
                                  placeholder="{{ __('ui.booking.reject_placeholder') }}"></textarea>
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
    @endforeach
@endsection
