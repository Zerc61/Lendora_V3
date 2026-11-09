@props(['title' => null, 'split' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>

    {{-- Font (sesuaikan dengan baris font di layout Anda sekarang) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS utama Anda, lalu CSS login baru SETELAHNYA --}}
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ldr-login.css') }}">
</head>
<body class="ldr-auth-body">

@if ($split)
    {{-- Mode baru: konten login mengatur tata letaknya sendiri --}}
    <main>
        {{ $slot }}
    </main>
@else
    {{-- Mode lama: dipakai halaman auth lain (register, lupa sandi, dst.) --}}
    <main class="ldr-auth">
        <div class="ldr-auth__panel">
            <a href="{{ url('/') }}" class="ldr-auth__brand">
                <span class="ldr-auth__mark" aria-hidden="true">
                    <x-icon name="aperture" :size="30" />
                </span>
                <span class="ldr-auth__brand-row">
                    <span class="ldr-auth__brand-name">{{ config('app.name') }}</span>
                    <span class="ldr-auth__badge">beta</span>
                </span>
            </a>

            {{ $slot }}
        </div>
    </main>
@endif

{{-- Toggle tampilkan/sembunyikan sandi — aman dipanggil berkali-kali --}}
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
                    // Catatan: .hidden = true TIDAK bekerja pada elemen SVG,
                    // wajib pakai setAttribute/removeAttribute.
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