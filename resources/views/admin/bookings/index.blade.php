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

    $showAll = ($filters['status'] ?? null) === null;
    $activeTab = $showAll ? null : $filters['status'];
@endphp

@section('content')
    <div class="ldr-page-head">
        <div>
            <h1>{{ __('ui.booking.queue') }}</h1>
            <p class="ldr-muted">{{ $bookings->total() }} pengajuan ditemukan</p>
        </div>

        <div class="ldr-page-head__actions">
            <a class="ldr-btn ldr-btn--secondary" href="{{ route('admin.bookings.calendar') }}">
                <x-icon name="calendar" :size="18" />
                {{ __('ui.booking.calendar') }}
            </a>
        </div>
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
        <div class="ldr-table-card">
            <div class="ldr-table-wrap">
                <table class="ldr-table ldr-table--bookings">
                    <thead>
                        <tr>
                            <th>{{ __('ui.booking.code') }}</th>
                            <th>{{ __('ui.booking.borrower') }}</th>
                            <th class="ldr-table__num">{{ __('ui.booking.assets') }}</th>
                            <th>{{ __('ui.booking.period') }}</th>
                            <th class="ldr-table__num">{{ __('ui.booking.duration') }}</th>
                            <th class="ldr-table__num">{{ __('ui.booking.value') }}</th>
                            <th>{{ __('ui.booking.filter_status') }}</th>
                            <th class="ldr-table__actions-head">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            @php
                                $conflict = $conflicts[$booking->id] ?? null;
                                $decidable = $canDecide[$booking->id] ?? false;
                                $blockReason = $limitExceeded[$booking->id] ?? null;
                                $rejectable = $decidable && $canRejectRole && $booking->status === App\Enums\BookingStatus::Pending;
                            @endphp
                            <tr>
                                <td class="ldr-table__code">
                                    <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->code }}</a>
                                </td>
                                <td>
                                    <span class="ldr-table__name">{{ $booking->borrower->name }}</span>
                                    <span class="ldr-table__sub">{{ $booking->purpose }}</span>
                                </td>
                                <td class="ldr-table__num">{{ $booking->items->count() }}</td>
                                <td class="ldr-table__num">{{ $formatRange($booking) }}</td>
                                <td class="ldr-table__num">{{ __('ui.booking.duration_days', ['count' => $booking->durationInDays()]) }}</td>
                                <td class="ldr-table__num">Rp {{ number_format($booking->totalAssetValue(), 0, ',', '.') }}</td>
                                <td><x-status-badge type="booking" :status="$booking->status->value" /></td>
                                <td>
                                    <div class="ldr-table__actions ldr-table__actions--text">
                                        <a class="ldr-btn ldr-btn--secondary ldr-btn--sm"
                                           href="{{ route('admin.bookings.show', $booking) }}">
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

                                    @if ($booking->status === App\Enums\BookingStatus::Pending && ! $decidable && $blockReason)
                                        <p class="ldr-table__note">{{ $blockReason }}</p>
                                    @endif
                                </td>
                            </tr>

                            @if ($conflict)
                                <tr class="ldr-table__banner-row">
                                    <td colspan="8">
                                        <x-conflict-banner :message="$conflict" />
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$bookings" />
        </div>
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
