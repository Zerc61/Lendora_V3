@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ldr-login.css') }}">
</head>
<body class="ldr-auth-body">
    <main class="ldr-auth">
        <div class="ldr-auth__shell">
            <a class="ldr-auth__brand" href="{{ url('/') }}">
                <span class="ldr-auth__brand-mark" aria-hidden="true">
                    <x-icon name="aperture" :size="24" />
                </span>
                <span class="ldr-auth__brand-text">
                    <span class="ldr-auth__brand-name">{{ config('app.name') }}</span>
                    <span class="ldr-auth__brand-tagline">{{ __('ui.auth.brand_tagline') }}</span>
                </span>
            </a>

            <div class="ldr-auth__panel">
                {{ $slot }}
            </div>

            <div class="ldr-auth__below">
                <div class="ldr-auth__links">
                    <span>
                        <x-icon name="book-open" :size="16" />
                        {{ __('ui.auth.rules_link') }}
                    </span>
                    <span aria-hidden="true">·</span>
                    <span>
                        <x-icon name="help-circle" :size="16" />
                        {{ __('ui.auth.help_link') }}
                    </span>
                </div>

                <p class="ldr-auth__version">{{ __('ui.auth.version') }}</p>
            </div>
        </div>
    </main>

    <script>
        (function () {
            function bindToggles() {
                document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
                    if (btn.dataset.toggleBound) return;
                    btn.dataset.toggleBound = '1';
                    btn.addEventListener('click', function () {
                        var input = document.getElementById(btn.getAttribute('aria-controls'));
                        if (!input) return;
                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        btn.setAttribute('aria-pressed', String(show));
                        btn.setAttribute('aria-label', show ? btn.dataset.labelHide : btn.dataset.labelShow);
                        var iconShow = btn.querySelector('[data-icon-show]');
                        var iconHide = btn.querySelector('[data-icon-hide]');
                        if (iconShow) { if (show) { iconShow.setAttribute('hidden', ''); } else { iconShow.removeAttribute('hidden'); } }
                        if (iconHide) { if (show) { iconHide.removeAttribute('hidden'); } else { iconHide.setAttribute('hidden', ''); } }
                    });
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindToggles);
            } else {
                bindToggles();
            }
        })();
    </script>
</body>
</html>
