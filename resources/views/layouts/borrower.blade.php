<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.nav.home')) · Lendora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/borrower.css') }}">
</head>
<body class="ldr-borrower-body">
    <a class="ldr-skip-link" href="#main-content">{{ __('ui.nav.skip_to_content') }}</a>

    @php
        $activeNav = trim($__env->yieldContent('nav'));
    @endphp

    @if (session('status') || session('error'))
        @php
            $toastMessage = session('error') ?? session('status');
        @endphp
        <div class="ldr-toast-stack" data-toast>
            <div class="ldr-toast {{ session('error') ? 'ldr-toast--danger' : 'ldr-toast--success' }}" role="status">
                <x-icon :name="session('error') ? 'alert-circle' : 'check-circle'" :size="18" />
                <p>{{ $toastMessage }}</p>
                <button type="button" class="ldr-toast__close" data-toast-close aria-label="{{ __('ui.common.close') }}">
                    <x-icon name="x" :size="16" />
                </button>
            </div>
        </div>
    @endif

    <div class="ldr-borrower">
        <header class="ldr-appbar">
            <a class="ldr-appbar__brand" href="{{ route('borrower.home') }}">Lendora</a>

            <a class="ldr-appbar__bell"
               href="{{ route('borrower.notifications.index') }}"
               aria-label="{{ __('ui.nav.notifications') }}">
                <x-icon name="bell" :size="22" />
                @if (($unreadNotifications ?? 0) > 0)
                    <span class="ldr-dot" aria-hidden="true"></span>
                @endif
            </a>
        </header>

        <main id="main-content" class="ldr-borrower-main">
            @yield('content')
        </main>

        <nav class="ldr-bottom-nav" aria-label="{{ __('ui.main_navigation') }}">
            <a class="ldr-bottom-nav__item {{ $activeNav === 'home' ? 'is-active' : '' }}"
               href="{{ route('borrower.home') }}"
               @if ($activeNav === 'home') aria-current="page" @endif>
                <x-icon name="home" :size="24" />
                <span>{{ __('ui.nav.home') }}</span>
            </a>

            <a class="ldr-bottom-nav__item {{ $activeNav === 'catalog' ? 'is-active' : '' }}"
               href="{{ route('borrower.catalog.index') }}"
               @if ($activeNav === 'catalog') aria-current="page" @endif>
                <x-icon name="search" :size="24" />
                <span>{{ __('ui.nav.catalog') }}</span>
            </a>

            <a class="ldr-bottom-nav__item {{ $activeNav === 'bookings' ? 'is-active' : '' }}"
               href="{{ route('borrower.bookings.index') }}"
               @if ($activeNav === 'bookings') aria-current="page" @endif>
                <x-icon name="clipboard-list" :size="24" />
                <span>{{ __('ui.nav.bookings') }}</span>
            </a>

            <a class="ldr-bottom-nav__item {{ $activeNav === 'notifications' ? 'is-active' : '' }}"
               href="{{ route('borrower.notifications.index') }}"
               @if ($activeNav === 'notifications') aria-current="page" @endif>
                <span class="ldr-bottom-nav__icon">
                    <x-icon name="bell" :size="24" />
                    @if (($unreadNotifications ?? 0) > 0)
                        <span class="ldr-dot ldr-dot--nav" aria-hidden="true"></span>
                    @endif
                </span>
                <span>{{ __('ui.nav.notifications') }}</span>
            </a>

            <a class="ldr-bottom-nav__item {{ $activeNav === 'profile' ? 'is-active' : '' }}"
               href="{{ route('borrower.profile.edit') }}"
               @if ($activeNav === 'profile') aria-current="page" @endif>
                <x-icon name="user" :size="24" />
                <span>{{ __('ui.nav.profile') }}</span>
            </a>
        </nav>
    </div>

    <script src="{{ asset('js/borrower.js') }}" defer></script>
</body>
</html>
