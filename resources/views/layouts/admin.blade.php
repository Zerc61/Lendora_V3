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
                ['label' => __('ui.nav.assets'), 'icon' => 'package', 'href' => route('admin.assets.index'), 'match' => 'admin.assets.*', 'roles' => ['operator', 'organization_admin', 'admin_prodi', 'technician']],
                ['label' => __('ui.nav.categories'), 'icon' => 'layers', 'href' => route('admin.categories.index'), 'match' => 'admin.categories.*', 'roles' => ['organization_admin', 'admin_prodi']],
            ],
            __('ui.sections.transactions') => [
                ['label' => __('ui.nav.booking_queue'), 'icon' => 'clipboard-list', 'href' => route('admin.bookings.index'), 'match' => 'admin.bookings.index', 'active_status' => 'not_all', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.all_bookings'), 'icon' => 'layers', 'href' => route('admin.bookings.index', ['status' => 'all']), 'match' => 'admin.bookings.index', 'active_status' => 'all', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.calendar'), 'icon' => 'calendar', 'href' => route('admin.bookings.calendar'), 'match' => 'admin.bookings.calendar', 'roles' => ['operator', 'organization_admin']],
            ],
            __('ui.sections.operations') => [
                ['label' => __('ui.nav.handovers'), 'icon' => 'arrow-left-right', 'href' => route('admin.handovers.index'), 'match' => 'admin.handovers.*', 'roles' => ['operator', 'organization_admin']],
                ['label' => __('ui.nav.inspections'), 'icon' => 'clipboard-check', 'href' => route('admin.inspections.index'), 'match' => 'admin.inspections.*', 'roles' => ['operator', 'organization_admin', 'technician']],
                ['label' => __('ui.nav.maintenances'), 'icon' => 'wrench', 'href' => route('admin.maintenances.index'), 'match' => 'admin.maintenances.*', 'roles' => ['operator', 'organization_admin', 'technician']],
            ],
            __('ui.sections.administration') => [
                ['label' => __('ui.nav.users'), 'icon' => 'users', 'href' => route('admin.users.index'), 'match' => 'admin.users.*', 'roles' => ['organization_admin']],
                ['label' => __('ui.nav.reports'), 'icon' => 'bar-chart', 'href' => route('admin.reports.index'), 'match' => 'admin.reports.*', 'roles' => ['operator', 'organization_admin', 'admin_prodi']],
                ['label' => __('ui.nav.settings'), 'icon' => 'settings', 'href' => route('admin.settings.edit'), 'match' => 'admin.settings.*', 'roles' => ['organization_admin']],
                ['label' => __('ui.nav.activity_logs'), 'icon' => 'activity', 'href' => route('admin.activity-logs.index'), 'match' => 'admin.activity-logs.*', 'roles' => ['organization_admin']],
            ],
        ];

        $visibleGroups = [];

        if ($role === 'operator') {
            $groups = [
                __('ui.sections.operator_counter') => [
                    ['label' => __('ui.nav.kiosk_scanner'), 'icon' => 'zap', 'href' => route('admin.kiosk.index'), 'match' => 'admin.kiosk.*', 'roles' => ['operator'], 'badge' => 'queue'],
                    ['label' => __('ui.nav.walk_in'), 'icon' => 'plus', 'href' => route('admin.bookings.walk-in'), 'match' => 'admin.bookings.walk-in*', 'roles' => ['operator']],
                    ['label' => __('ui.nav.approval_queue'), 'icon' => 'clipboard-list', 'href' => route('admin.bookings.approval'), 'match' => 'admin.bookings.approval', 'roles' => ['operator'], 'badge' => 'approvals'],
                    ['label' => __('ui.nav.handovers'), 'icon' => 'arrow-left-right', 'href' => route('admin.handovers.index'), 'match' => 'admin.handovers.*', 'roles' => ['operator']],
                    ['label' => __('ui.nav.inspections'), 'icon' => 'clipboard-check', 'href' => route('admin.inspections.index'), 'match' => 'admin.inspections.*', 'roles' => ['operator']],
                    ['label' => __('ui.nav.penalties'), 'icon' => 'book-open', 'href' => route('admin.penalties.index'), 'match' => 'admin.penalties.*', 'roles' => ['operator']],
                ],
                __('ui.sections.inventory_schedule') => [
                    ['label' => __('ui.nav.assets'), 'icon' => 'package', 'href' => route('admin.assets.index'), 'match' => 'admin.assets.*', 'roles' => ['operator']],
                    ['label' => __('ui.nav.maintenances'), 'icon' => 'wrench', 'href' => route('admin.maintenances.index'), 'match' => 'admin.maintenances.*', 'roles' => ['operator']],
                    ['label' => __('ui.nav.calendar'), 'icon' => 'calendar', 'href' => route('admin.bookings.calendar'), 'match' => 'admin.bookings.calendar', 'roles' => ['operator']],
                ],
            ];
        }

        if ($role === 'organization_admin') {
            $groups = [
                __('ui.sections.inventory') => [
                    ['label' => __('ui.nav.assets'), 'icon' => 'package', 'href' => route('admin.assets.index'), 'match' => 'admin.assets.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.categories'), 'icon' => 'layers', 'href' => route('admin.categories.index'), 'match' => 'admin.categories.*', 'roles' => ['organization_admin']],
                ],
                __('ui.sections.borrowings') => [
                    ['label' => __('ui.nav.booking_queue'), 'icon' => 'clipboard-list', 'href' => route('admin.bookings.index'), 'match' => 'admin.bookings.index', 'active_status' => 'not_all', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.all_bookings'), 'icon' => 'layers', 'href' => route('admin.bookings.index', ['status' => 'all']), 'match' => 'admin.bookings.index', 'active_status' => 'all', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.calendar'), 'icon' => 'calendar', 'href' => route('admin.bookings.calendar'), 'match' => 'admin.bookings.calendar', 'roles' => ['organization_admin']],
                ],
                __('ui.sections.operations') => [
                    ['label' => __('ui.nav.handovers'), 'icon' => 'arrow-left-right', 'href' => route('admin.handovers.index'), 'match' => 'admin.handovers.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.inspections'), 'icon' => 'clipboard-check', 'href' => route('admin.inspections.index'), 'match' => 'admin.inspections.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.maintenances'), 'icon' => 'wrench', 'href' => route('admin.maintenances.index'), 'match' => 'admin.maintenances.*', 'roles' => ['organization_admin']],
                ],
                __('ui.sections.organization') => [
                    ['label' => __('ui.nav.users_roles'), 'icon' => 'users', 'href' => route('admin.users.index'), 'match' => 'admin.users.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.reports_analytics'), 'icon' => 'bar-chart', 'href' => route('admin.reports.index'), 'match' => 'admin.reports.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.activity_logs'), 'icon' => 'activity', 'href' => route('admin.activity-logs.index'), 'match' => 'admin.activity-logs.*', 'roles' => ['organization_admin']],
                    ['label' => __('ui.nav.settings'), 'icon' => 'settings', 'href' => route('admin.settings.edit'), 'match' => 'admin.settings.*', 'roles' => ['organization_admin']],
                ],
            ];
        }

        if ($role === 'admin_prodi') {
            $groups = [
                __('ui.sections.prodi_authorization') => [
                    ['label' => __('ui.nav.otorisasi_khusus'), 'icon' => 'shield-check', 'href' => route('admin.bookings.approval'), 'match' => 'admin.bookings.approval', 'roles' => ['admin_prodi'], 'badge' => 'special_approvals'],
                ],
                __('ui.sections.prodi_monitoring') => [
                    ['label' => __('ui.nav.course_quota'), 'icon' => 'bar-chart', 'href' => route('admin.courses.index'), 'match' => 'admin.courses.*', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.practicum'), 'icon' => 'calendar', 'href' => route('admin.practicum.index'), 'match' => 'admin.practicum.*', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.students'), 'icon' => 'users', 'href' => route('admin.students.index'), 'match' => 'admin.students.*', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.assets'), 'icon' => 'package', 'href' => route('admin.assets.index'), 'match' => 'admin.assets.*', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.categories'), 'icon' => 'layers', 'href' => route('admin.categories.index'), 'match' => 'admin.categories.*', 'roles' => ['admin_prodi']],
                ],
                __('ui.sections.reports_documents') => [
                    ['label' => __('ui.nav.semester_report'), 'icon' => 'clipboard-list', 'href' => route('admin.reports.semester'), 'match' => 'admin.reports.semester*', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.reports'), 'icon' => 'bar-chart', 'href' => route('admin.reports.index'), 'match' => 'admin.reports.index', 'roles' => ['admin_prodi']],
                    ['label' => __('ui.nav.recommendations'), 'icon' => 'book-open', 'href' => route('admin.recommendations.index'), 'match' => 'admin.recommendations.*', 'roles' => ['admin_prodi']],
                ],
            ];
        }

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
                @if ($role === 'operator')
                    <div class="ldr-sidebar__group">
                        <p class="ldr-sidebar__group-label ldr-sidebar__group-label--static">{{ __('ui.sections.main') }}</p>
                        <ul class="ldr-sidebar__list">
                            <li>
                                <a class="ldr-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                                   href="{{ route('admin.dashboard') }}"
                                   @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                                    <x-icon name="home" :size="20" />
                                    <span class="ldr-sidebar__label">{{ __('ui.nav.dashboard_loket') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="ldr-sidebar__dashboard {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                       href="{{ route('admin.dashboard') }}"
                       @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <x-icon name="home" :size="20" />
                        <span class="ldr-sidebar__label">{{ $role === 'admin_prodi' ? __('ui.nav.dashboard_prodi') : __('ui.nav.dashboard') }}</span>
                    </a>
                @endif

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

                                    $hasNotice = isset($item['badge']) && (int) ($sidebarCounts[$item['badge']] ?? 0) > 0;
                                @endphp
                                <li>
                                    <a class="ldr-sidebar__link {{ $isActive ? 'is-active' : '' }} {{ $hasNotice ? 'has-notice' : '' }}"
                                       href="{{ $item['href'] }}"
                                       @if ($isActive) aria-current="page" @endif>
                                        <x-icon :name="$item['icon']" :size="20" />
                                        <span class="ldr-sidebar__label">{{ $item['label'] }}</span>
                                        @if ($hasNotice)
                                            <span class="ldr-sidebar__badge">{{ $sidebarCounts[$item['badge']] }}</span>
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

                <form class="ldr-admin__search" method="GET" action="{{ route('admin.search') }}" role="search">
                    <label class="ldr-sr-only" for="admin-search">{{ __('ui.search.placeholder') }}</label>
                    <x-icon name="search" :size="16" class="ldr-admin__search-icon" />
                    <input id="admin-search"
                           class="ldr-input"
                           type="search"
                           name="q"
                           value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                           minlength="2"
                           placeholder="{{ __('ui.search.placeholder') }}">
                </form>

                @if ($adminShift !== null)
                    <span class="ldr-admin__shift {{ $adminShift['active'] ? 'is-active' : 'is-idle' }}">
                        <span class="ldr-admin__shift-dot" aria-hidden="true"></span>
                        {{ __('ui.dashboard.operator.shift_badge', ['label' => $adminShift['label']]) }}
                    </span>
                @endif

                <a class="ldr-icon-btn ldr-admin__bell"
                   href="{{ route('admin.notifications.index') }}"
                   aria-label="{{ __('ui.notification.open', ['count' => $unreadNotifications ?? 0]) }}">
                    <x-icon name="bell" :size="20" />
                    @if (($unreadNotifications ?? 0) > 0)
                        <span class="ldr-dot" aria-hidden="true"></span>
                    @endif
                </a>

                <a class="ldr-admin__user" href="{{ route('admin.profile.edit') }}">
                    <span class="ldr-admin__user-name">{{ $user->name }}</span>
                    <span class="ldr-badge ldr-badge--primary">{{ $user->role->name }}</span>
                </a>
            </header>

            <main id="main-content" class="ldr-admin__main">
                @unless (request()->routeIs('admin.dashboard'))
                    <nav class="ldr-breadcrumb" aria-label="{{ __('ui.breadcrumb') }}">
                        <a href="{{ route('admin.dashboard') }}">{{ __('ui.nav.dashboard') }}</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page">@yield('title', __('ui.nav.dashboard'))</span>
                    </nav>
                @endunless

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
