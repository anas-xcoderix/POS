<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; margin: 20px; }
        h1 { font-size: 16px; color: #ea580c; margin: 0 0 4px; }
        .sub { font-size: 10px; color: #64748b; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 5px 6px; }
        th { background: #f8fafc; font-weight: bold; }
        .rtl { direction: rtl; text-align: right; }
        .ltr { direction: ltr; text-align: left; }
    </style>
</head>
<body class="{{ ($rtl ?? is_rtl()) ? 'rtl' : 'ltr' }}">
    <h1>{{ config('app.name') }}</h1>
    <div class="sub">{{ $title }} · {{ now()->format('Y-m-d H:i') }}</div>
    {{ $slot }}
</body>
</html>
