<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title !== '' ? $title : __('ui.auth.login_title') }} · Lendora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
</head>
<body class="ldr-auth-body">
    <main class="ldr-auth">
        <div class="ldr-auth__panel">
            <a class="ldr-auth__brand" href="{{ url('/') }}">Lendora</a>
            <p class="ldr-auth__tagline">{{ __('ui.brand_tagline') }}</p>

            <div class="ldr-auth__card">
                {{ $slot }}
            </div>
        </div>
    </main>
</body>
</html>
