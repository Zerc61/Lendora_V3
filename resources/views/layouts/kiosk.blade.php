<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kiosk') · Lendora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="ldr-kiosk-body">
    <header class="ldr-kiosk__bar">
        <a class="ldr-kiosk__brand" href="{{ url('/kiosk') }}">
            <span class="ldr-kiosk__brand-mark"><x-icon name="camera" :size="20" /></span>
            <span>
                <strong>Lendora</strong>
                <span class="ldr-kiosk__brand-sub">{{ __('ui.kiosk.portal') }}</span>
            </span>
        </a>

        <span class="ldr-kiosk__spacer"></span>

        <span class="ldr-kiosk__clock" data-clock aria-live="off">
            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
        </span>

        @yield('bar')
    </header>

    <main class="ldr-kiosk" id="main-content">
        @if (session('status'))
            <div class="ldr-alert ldr-alert--success" role="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
