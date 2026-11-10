@php
    $pageClass = trim($__env->yieldContent('pdf_class'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dokumen') · Lendora</title>
    <style>
        {!! file_get_contents(public_path('css/lendora.css')) !!}
    </style>
    <style>
        @page { margin: 18mm 14mm; }

        body {
            margin: 0;
            background: #FFFFFF;
            color: #262626;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .ldr-pdf {
            display: block;
        }

        .ldr-pdf h1 {
            font-size: 16px;
            margin: 0 0 4px;
        }

        .ldr-pdf h2 {
            font-size: 12px;
            margin: 14px 0 6px;
        }

        .ldr-pdf .ldr-muted {
            color: #595959;
        }

        .ldr-pdf table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .ldr-pdf th,
        .ldr-pdf td {
            border: 1px solid #D9D9D9;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }

        .ldr-pdf th {
            background: #FFF7ED;
        }

        .ldr-pdf .ldr-kpi,
        .ldr-pdf .ldr-card,
        .ldr-pdf .ldr-print-header,
        .ldr-pdf .ldr-print-page {
            background: #FFFFFF;
            border: 0;
            box-shadow: none;
            padding: 0;
            margin: 0 0 10px;
        }

        .ldr-pdf .ldr-btn,
        .ldr-pdf .ldr-print-actions {
            display: none;
        }

        .ldr-pdf .ldr-print-header {
            border-bottom: 2px solid #F97316;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .ldr-pdf .ldr-grid {
            display: block;
        }
    </style>
</head>
<body class="ldr-pdf {{ $pageClass }}">
    <main class="ldr-pdf">
        @yield('content')
    </main>
</body>
</html>
