<!DOCTYPE html>
<html lang="{{ ($rtl ?? is_rtl()) ? 'ar' : 'en' }}" dir="{{ ($rtl ?? is_rtl()) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('reports.pdf._styles', ['isAr' => ($rtl ?? is_rtl())])
    <style>
        table.rpt { margin-top: 8px; }
    </style>
</head>
<body>
    @php
        $fakeMeta = [
            'company' => config('app.name'),
            'title' => $title,
            'title_en' => $title,
            'title_ar' => $title,
            'generated_at' => now()->format('Y-m-d H:i'),
            'printed_by' => auth()->user()?->name,
            'category' => 'masters',
            'legacy' => null,
            'filter_display' => [],
            'row_count' => null,
        ];
    @endphp
    @include('reports.pdf._header', ['data' => ['meta' => $fakeMeta], 'isAr' => ($rtl ?? is_rtl())])
    <table class="rpt">
        {{ $slot }}
    </table>
    @include('reports.pdf._footer', ['data' => ['meta' => $fakeMeta], 'isAr' => ($rtl ?? is_rtl())])
</body>
</html>
