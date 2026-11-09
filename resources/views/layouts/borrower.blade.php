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
        $borrowerUser = $user ?? auth()->user();
        $navItems = [
            ['key' => 'home', 'label' => __('ui.nav.home'), 'icon' => 'home', 'route' => 'borrower.home'],
            ['key' => 'catalog', 'label' => __('ui.nav.catalog'), 'icon' => 'search', 'route' => 'borrower.catalog.index'],
            ['key' => 'bookings', 'label' => __('ui.nav.bookings'), 'icon' => 'clipboard-list', 'route' => 'borrower.bookings.index'],
            ['key' => 'notifications', 'label' => __('ui.nav.notifications'), 'icon' => 'bell', 'route' => 'borrower.notifications.index'],
            ['key' => 'profile', 'label' => __('ui.nav.profile'), 'icon' => 'user', 'route' => 'borrower.profile.edit'],
        ];
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
        <header class="ldr-portal-header">
            <div class="ldr-portal-header__inner">
                <a class="ldr-portal-brand" href="{{ route('borrower.home') }}">
                    <span class="ldr-portal-brand__mark" aria-hidden="true">
                        <x-icon name="camera" :size="18" />
                    </span>
                    <span class="ldr-portal-brand__name">Lendora</span>
                    <span class="ldr-portal-brand__scope">{{ __('ui.borrower.portal_label') }}</span>
                </a>

                <nav class="ldr-portal-nav" aria-label="{{ __('ui.main_navigation') }}">
                    @foreach ($navItems as $item)
                        <a class="ldr-portal-nav__link {{ $activeNav === $item['key'] ? 'is-active' : '' }}"
                           href="{{ route($item['route']) }}"
                           @if ($activeNav === $item['key']) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="ldr-portal-actions">
                    <form class="ldr-portal-search" method="GET" action="{{ route('borrower.catalog.index') }}" role="search">
                        <label class="ldr-sr-only" for="portal-search">{{ __('ui.catalog.search_placeholder') }}</label>
                        <x-icon name="search" :size="16" />
                        <input id="portal-search"
                               type="search"
                               name="search"
                               value="{{ request()->routeIs('borrower.catalog.index') ? request('search') : '' }}"
                               placeholder="{{ __('ui.catalog.search_placeholder') }}">
                    </form>

                    <a class="ldr-btn ldr-btn--primary ldr-btn--sm ldr-portal-actions__cta" href="{{ route('borrower.catalog.index') }}">
                        <x-icon name="plus" :size="16" />
                        {{ __('ui.booking.new') }}
                    </a>

                    <a class="ldr-icon-btn ldr-portal-actions__bell"
                       href="{{ route('borrower.notifications.index') }}"
                       aria-label="{{ __('ui.nav.notifications') }}">
                        <x-icon name="bell" :size="20" />
                        @if (($unreadNotifications ?? 0) > 0)
                            <span class="ldr-dot" aria-hidden="true"></span>
                        @endif
                    </a>

                    <a class="ldr-portal-user" href="{{ route('borrower.profile.edit') }}">
                        <span class="ldr-portal-user__text">
                            <span class="ldr-portal-user__name">{{ $borrowerUser->name }}</span>
                            <span class="ldr-portal-user__role">
                                @if ($borrowerUser->nim)
                                    {{ $borrowerUser->nim }}
                                    @if ($borrowerUser->study_program)
                                        · {{ $borrowerUser->study_program }}
                                    @endif
                                @else
                                    {{ $borrowerUser->role?->name }}
                                @endif
                            </span>
                        </span>
                        <span class="ldr-portal-user__avatar" aria-hidden="true">{{ mb_substr($borrowerUser->name, 0, 1) }}</span>
                    </a>
                </div>
            </div>
        </header>

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
            @foreach ($navItems as $item)
                <a class="ldr-bottom-nav__item {{ $activeNav === $item['key'] ? 'is-active' : '' }}"
                   href="{{ route($item['route']) }}"
                   @if ($activeNav === $item['key']) aria-current="page" @endif>
                    <span class="ldr-bottom-nav__icon">
                        <x-icon :name="$item['icon']" :size="24" />
                        @if ($item['key'] === 'notifications' && ($unreadNotifications ?? 0) > 0)
                            <span class="ldr-dot ldr-dot--nav" aria-hidden="true"></span>
                        @endif
                    </span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <script src="{{ asset('js/borrower.js') }}" defer></script>
</body>
</html>
