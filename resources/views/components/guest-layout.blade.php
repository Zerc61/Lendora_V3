@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F97316">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/lendora-icon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ldr-login.css') }}">
</head>
<body class="ldr-auth-body">
    <a class="ldr-skip-link" href="#main-content">{{ __('ui.nav.skip_to_content') }}</a>

    <main id="main-content" class="ldr-auth">
        <div class="ldr-auth__shell">
            {{ $slot }}
        </div>
    </main>

    <script src="{{ asset('js/borrower.js') }}" defer></script>
</body>
</html>