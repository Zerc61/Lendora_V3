@extends('layouts.borrower')

@section('title', __('ui.nav.home'))
@section('nav', 'home')

@section('content')
    @php
        $activeItem = $activeBooking?->items->first();
        $activeAsset = $activeItem?->asset;
        $activePhoto = $activeAsset?->photos->first();
    @endphp

    <div class="ldr-home">
        <section class="ldr-home__hero">
            <div class="ldr-home__hero-copy">
                <p class="ldr-home__eyebrow">{{ __('ui.borrower.portal_label') }}</p>
                <h1 class="ldr-display">{{ __('ui.borrower.greeting', ['name' => $user->name]) }}</h1>
                <p>{{ __('ui.borrower.portal_subtitle') }}</p>

                <div class="ldr-home__hero-meta">
                    <span>
                        <x-icon name="calendar" :size="15" />
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span>
                        <x-icon name="package" :size="15" />
                        {{ __('ui.borrower.available_units', ['count' => $availableAssets]) }}
                    </span>
                </div>
            </div>

            <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.catalog.index') }}">
                <x-icon name="search" :size="18" />
                {{ __('ui.nav.catalog') }}
            </a>
        </section>

        <section class="ldr-home__stats" aria-label="{{ __('ui.borrower.loan_summary') }}">
            <a class="ldr-home-stat" href="{{ route('borrower.bookings.index', ['status' => 'active']) }}">
                <span class="ldr-home-stat__icon ldr-home-stat__icon--info">
                    <x-icon name="layers" :size="20" />
                </span>
                <span class="ldr-home-stat__value">{{ $summary['active'] }}</span>
                <span class="ldr-home-stat__label">{{ __('ui.booking_status.active') }}</span>
            </a>

            <a class="ldr-home-stat" href="{{ route('borrower.bookings.index', ['status' => 'pending']) }}">
                <span class="ldr-home-stat__icon ldr-home-stat__icon--warning">
                    <x-icon name="clipboard-list" :size="20" />
                </span>
                <span class="ldr-home-stat__value">{{ $summary['pending'] }}</span>
                <span class="ldr-home-stat__label">{{ __('ui.booking_status.pending') }}</span>
            </a>

            <a class="ldr-home-stat" href="{{ route('borrower.bookings.index', ['status' => 'approved']) }}">
                <span class="ldr-home-stat__icon ldr-home-stat__icon--primary">
                    <x-icon name="check-circle" :size="20" />
                </span>
                <span class="ldr-home-stat__value">{{ $summary['approved'] }}</span>
                <span class="ldr-home-stat__label">{{ __('ui.booking_status.approved') }}</span>
            </a>

            <a class="ldr-home-stat" href="{{ route('borrower.bookings.index', ['status' => 'returned']) }}">
                <span class="ldr-home-stat__icon ldr-home-stat__icon--success">
                    <x-icon name="check-circle" :size="20" />
                </span>
                <span class="ldr-home-stat__value">{{ $summary['returned'] }}</span>
                <span class="ldr-home-stat__label">{{ __('ui.booking_status.returned') }}</span>
            </a>
        </section>

        @if ($activeBooking !== null)
            <section class="ldr-active-loan" aria-labelledby="active-loan-title">
                <div class="ldr-active-loan__head">
                    <span class="ldr-active-loan__label">
                        <x-icon name="zap" :size="16" />
                        {{ __('ui.borrower.active_loan') }}
                    </span>
                    <x-status-badge type="booking" :status="$activeBooking->status->value" />
                </div>

                <div class="ldr-active-loan__body">
                    <span class="ldr-active-loan__media">
                        @if ($activePhoto)
                            <img src="{{ Illuminate\Support\Facades\Storage::url($activePhoto->path) }}"
                                 alt="{{ $activeAsset->name }}"
                                 loading="lazy"
                                 width="140"
                                 height="140">
                        @else
                            <x-icon name="camera" :size="28" />
                        @endif
                    </span>

                    <div class="ldr-active-loan__info">
                        <span class="ldr-mono ldr-muted">{{ $activeBooking->code }}</span>
                        <h2 id="active-loan-title">{{ $activeAsset?->name ?? __('ui.booking.assets') }}</h2>

                        <div class="ldr-active-loan__meta">
                            <span>
                                <x-icon name="calendar" :size="15" />
                                {{ $activeBooking->requested_start_date->copy()->locale('id')->translatedFormat('d M') }}
                                –
                                {{ $activeBooking->requested_end_date->copy()->locale('id')->translatedFormat('d M Y') }}
                            </span>

                            @if ($activeAsset?->code)
                                <span class="ldr-mono">{{ $activeAsset->code }}</span>
                            @endif

                            @if ($activeAsset?->location)
                                <span>
                                    <x-icon name="package" :size="15" />
                                    {{ $activeAsset->location }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="ldr-active-loan__footer">
                    @if ($dueInfo !== null)
                        <span class="ldr-active-loan__due {{ $dueInfo['is_overdue'] ? 'is-overdue' : '' }}">
                            <x-icon name="alert-circle" :size="16" />
                            {{ __('ui.borrower.due') }}:
                            {{ $dueInfo['due_at']->locale('id')->translatedFormat('d M Y H:i') }}
                            @if ($dueInfo['is_overdue'])
                                · {{ __('ui.borrower.overdue') }}
                            @else
                                · {{ __('ui.borrower.days_left', ['count' => $dueInfo['days_left']]) }}
                            @endif
                        </span>
                    @else
                        <span class="ldr-muted">{{ __('ui.booking.due_at') }}: —</span>
                    @endif

                    <a class="ldr-btn ldr-btn--primary" href="{{ route('borrower.bookings.show', $activeBooking) }}">
                        {{ __('ui.booking.detail_action') }}
                        <x-icon name="arrow-right" :size="16" />
                    </a>
                </div>
            </section>
        @else
            <x-empty-state
                icon="package"
                :title="__('ui.borrower.no_active_title')"
                :description="__('ui.borrower.no_active_description')"
            >
                <a class="ldr-btn ldr-btn--secondary" href="{{ route('borrower.catalog.index') }}">
                    {{ __('ui.nav.catalog') }}
                </a>
            </x-empty-state>
        @endif

        <section class="ldr-home__section" aria-labelledby="recommendation-title">
            <div class="ldr-home__section-head">
                <div>
                    <h2 id="recommendation-title">{{ __('ui.borrower.recommendations') }}</h2>
                    <p class="ldr-muted">{{ __('ui.borrower.recommendations_hint') }}</p>
                </div>
                <a class="ldr-home__link" href="{{ route('borrower.catalog.index') }}">
                    {{ __('ui.common.all') }}
                    <x-icon name="chevron-right" :size="16" />
                </a>
            </div>

            <div class="ldr-asset-grid">
                @foreach ($recommendations as $asset)
                    <x-asset-card :asset="$asset" :href="route('borrower.catalog.show', $asset)" />
                @endforeach
            </div>
        </section>

        @if ($announcement !== '')
            <section class="ldr-announcement" aria-labelledby="announcement-title">
                <span class="ldr-announcement__icon" aria-hidden="true">
                    <x-icon name="bell" :size="20" />
                </span>
                <div>
                    <h2 id="announcement-title">{{ __('ui.borrower.announcement') }}</h2>
                    <p>{{ $announcement }}</p>
                </div>
            </section>
        @endif
    </div>
@endsection
