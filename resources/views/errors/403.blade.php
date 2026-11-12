@php
    $user = auth()->user();
    $isActive = $user && $user->status === 'active';

    $homeUrl = match (true) {
        ! $isActive => null,
        $user?->hasRole('borrower') => route('borrower.home'),
        (bool) $user => route('admin.dashboard'),
        default => url('/'),
    };
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.error.forbidden_title') }} · Lendora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
</head>
<body>
    <main class="ldr-error">
        <div class="ldr-error__panel">
            <span class="ldr-error__icon"><x-icon name="alert-circle" :size="26" /></span>
            <h1>{{ __('ui.error.forbidden_title') }}</h1>
            <p>{{ $isActive ? __('ui.error.forbidden_description') : __('messages.suspended') }}</p>
            <div class="ldr-error__actions">
                @if ($homeUrl)
                    <a class="ldr-btn ldr-btn--primary" href="{{ $homeUrl }}">{{ __('ui.error.back_home') }}</a>
                @endif
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ldr-btn ldr-btn--ghost">{{ __('ui.auth.logout') }}</button>
                    </form>
                @endauth
            </div>
        </div>
    </main>
</body>
</html>
