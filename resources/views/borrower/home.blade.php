@extends('layouts.borrower')

@section('title', __('ui.nav.home'))
@section('nav', 'home')

@section('content')
    <div class="ldr-home">
        <x-borrower.hero-status :user="$user" :activeBooking="$activeBooking" :dueInfo="$dueInfo" :quota="$quota" :compliance="$compliance" />

        <x-borrower.stat-strip :activeLoans="$summary['active']" :penalties="$sanctions['total_penalties'] ?? 0" :quotaRemaining="$quota['remaining_hours'] ?? '—'" />

        <x-borrower.active-loan-card :booking="$activeBooking" :dueInfo="$dueInfo" :stepperData="$stepperData" />

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

        @if ($announcements->isNotEmpty())
            <x-borrower.announcement-list :announcements="$announcements" />
        @elseif ($announcement !== '')
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

        <x-borrower.quick-guide />
    </div>
@endsection