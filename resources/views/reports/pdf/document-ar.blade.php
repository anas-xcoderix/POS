<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $data['meta']['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 24px; direction: rtl; text-align: right; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #ea580c; }
        .meta { font-size: 10px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: right; }
        th { background: #f8fafc; font-weight: bold; }
        .text-right { text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $data['meta']['company'] }}</h1>
    <div class="meta">
        <strong>{{ $data['meta']['title_ar'] ?? $data['meta']['title'] }}</strong>
        @if($def['legacy'] ?? null) · {{ $def['legacy'] }} @endif
        · {{ $data['meta']['generated_at'] }}
    </div>

    @include('reports.partials.body', ['data' => $data, 'isAr' => true])
</body>
</html>
