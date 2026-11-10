<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cetak') · Lendora</title>
    <link rel="stylesheet" href="{{ asset('css/lendora.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="ldr-print-body">
    <main class="ldr-print">
        @yield('content')
    </main>
</body>
</html>
