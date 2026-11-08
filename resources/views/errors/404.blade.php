<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.error.not_found_title') }} · Lendora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
</head>
<body>
    <main class="ldr-error">
        <div class="ldr-error__panel">
            <span class="ldr-error__icon"><x-icon name="search" :size="26" /></span>
            <h1>{{ __('ui.error.not_found_title') }}</h1>
            <p>{{ __('ui.error.not_found_description') }}</p>
            <a class="ldr-btn ldr-btn--primary" href="{{ url('/') }}">{{ __('ui.error.back_home') }}</a>
        </div>
    </main>
</body>
</html>
