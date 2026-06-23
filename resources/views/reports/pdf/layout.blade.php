<!DOCTYPE html>
<html lang="{{ ($isAr ?? false) ? 'ar' : 'en' }}" dir="{{ ($isAr ?? false) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ($isAr ?? false) ? ($data['meta']['title_ar'] ?? $data['meta']['title']) : $data['meta']['title'] }}</title>
    @include('reports.pdf._styles', ['isAr' => $isAr ?? false])
</head>
<body>
    @include('reports.pdf._header', ['data' => $data, 'isAr' => $isAr ?? false])
    @include('reports.pdf._body', ['data' => $data, 'isAr' => $isAr ?? false])
    @include('reports.pdf._footer', ['data' => $data, 'isAr' => $isAr ?? false])
</body>
</html>
