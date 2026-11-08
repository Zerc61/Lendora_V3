<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.nav.dashboard')) · Lendora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="ldr-admin-body">
    <a class="ldr-skip-link" href="#main-content">{{ __('ui.nav.skip_to_content') }}</a>

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

    @php
        $user = $user ?? auth()->user();
        $role = $user->role->slug;
        $groups = [
            __('ui.sections.inventory') => [
                ['label' => __('ui.nav.assets'), 'icon' => 'package', 'href' => route('admin.assets.index'), 'match' => 'admin.assets.*', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.categories'), 'icon' => 'layers', 'href' => route('admin.categories.index'), 'match' => 'admin.categories.*', 'roles' => ['organization_admin', 'admin_prodi']],
            ],
            __('ui.sections.transactions') => [
                ['label' => __('ui.nav.booking_queue'), 'icon' => 'clipboard-list', 'href' => route('admin.bookings.index'), 'match' => 'admin.bookings.index', 'active_status' => 'not_all', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.all_bookings'), 'icon' => 'layers', 'href' => route('admin.bookings.index', ['status' => 'all']), 'match' => 'admin.bookings.index', 'active_status' => 'all', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.calendar'), 'icon' => 'calendar', 'href' => route('admin.bookings.calendar'), 'match' => 'admin.bookings.calendar', 'roles' => ['operator', 'organization_admin']],
            ],
            __('ui.sections.operations') => [
                ['label' => __('ui.nav.handovers'), 'icon' => 'arrow-left-right', 'href' => '#', 'roles' => ['operator']],
                ['label' => __('ui.nav.inspections'), 'icon' => 'clipboard-check', 'href' => '#', 'roles' => ['operator', 'organization_admin', 'technician']],
                ['label' => __('ui.nav.maintenances'), 'icon' => 'wrench', 'href' => '#', 'roles' => ['operator', 'organization_admin', 'technician']],
            ],
            __('ui.sections.administration') => [
                ['label' => __('ui.nav.users'), 'icon' => 'users', 'href' => '#', 'roles' => ['organization_admin']],
                ['label' => __('ui.nav.reports'), 'icon' => 'bar-chart', 'href' => '#', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.settings'), 'icon' => 'settings', 'href' => '#', 'roles' => ['operator', 'organization_admin']],
                ['label' => __('ui.nav.activity_logs'), 'icon' => 'activity', 'href' => '#', 'roles' => ['organization_admin']],
            ],
        ];

        $visibleGroups = [];

        foreach ($groups as $label => $items) {
            $items = array_values(array_filter(
                $items,
                fn (array $item) => in_array($role, $item['roles'], true),
            ));

            if ($items !== []) {
                $visibleGroups[$label] = $items;
            }
        }
    @endphp

    <div class="ldr-admin" data-admin-shell>
        <aside class="ldr-admin__sidebar" id="admin-sidebar">
            <a class="ldr-sidebar__brand" href="{{ route('admin.dashboard') }}">
                <span class="ldr-sidebar__brand-mark"><x-icon name="camera" :size="20" /></span>
                <span class="ldr-sidebar__brand-name">
                    Lendora
                    <span class="ldr-sidebar__brand-scope">Admin</span>
                </span>
            </a>

            <nav class="ldr-sidebar__nav" aria-label="{{ __('ui.main_navigation') }}">
                <a class="ldr-sidebar__dashboard {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                   href="{{ route('admin.dashboard') }}"
                   @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <x-icon name="home" :size="20" />
                    <span class="ldr-sidebar__label">{{ __('ui.nav.dashboard') }}</span>
                </a>

                @foreach ($visibleGroups as $label => $items)
                    <div class="ldr-sidebar__group" data-menu-group>
                        <button type="button"
                                class="ldr-sidebar__group-label"
                                data-menu-toggle
                                aria-expanded="true">
                            <span>{{ $label }}</span>
                            <x-icon name="chevron-down" :size="16" class="ldr-sidebar__chevron" />
                        </button>

                        <ul class="ldr-sidebar__list">
                            @foreach ($items as $item)
                                @php
                                    $isActive = isset($item['match']) && request()->routeIs($item['match']);

                                    if ($isActive && isset($item['active_status'])) {
                                        $isActive = $item['active_status'] === 'all'
                                            ? request()->query('status') === 'all'
                                            : request()->query('status') !== 'all';
                                    }
                                @endphp
                                <li>
                                    <a class="ldr-sidebar__link {{ $isActive ? 'is-active' : '' }}"
                                       href="{{ $item['href'] }}"
                                       @if ($item['href'] === '#') title="{{ __('ui.nav.coming_soon') }}" @endif
                                       @if ($isActive) aria-current="page" @endif>
                                        <x-icon :name="$item['icon']" :size="20" />
                                        <span class="ldr-sidebar__label">{{ $item['label'] }}</span>
                                        @if ($item['href'] === '#')
                                            <span class="ldr-sidebar__soon">{{ __('ui.nav.coming_soon') }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="ldr-sidebar__footer">
                <span class="ldr-sidebar__user-name">{{ $user->name }}</span>
                <span class="ldr-badge ldr-badge--primary">{{ $user->role->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="ldr-sidebar__logout">
                        <x-icon name="log-out" :size="18" />
                        <span>{{ __('ui.auth.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="ldr-admin__backdrop" data-sidebar-backdrop></div>

        <div class="ldr-admin__content">
            <header class="ldr-admin__topbar">
                <button type="button"
                        class="ldr-icon-btn"
                        data-sidebar-toggle
                        aria-controls="admin-sidebar"
                        aria-expanded="true"
                        aria-label="{{ __('ui.admin.toggle_sidebar') }}">
                    <x-icon name="menu" :size="20" />
                </button>

                <span class="ldr-admin__title">@yield('title', __('ui.nav.dashboard'))</span>

                <div class="ldr-admin__user">
                    <span class="ldr-admin__user-name">{{ $user->name }}</span>
                    <span class="ldr-badge ldr-badge--primary">{{ $user->role->name }}</span>
                </div>
            </header>

            <main id="main-content" class="ldr-admin__main">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
