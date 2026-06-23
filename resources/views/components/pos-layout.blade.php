@props(['title' => null])
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @if($title) — {{ $title }} @endif</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|noto-sans-arabic:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-100 {{ $isRtl ? 'erp-rtl' : 'erp-ltr' }} min-h-screen">
    @if(session('success'))
        <div class="bg-emerald-600 px-4 py-2 text-center text-sm font-medium text-white">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-600 px-4 py-2 text-center text-sm font-medium text-white">{{ session('error') }}</div>
    @endif
    {{ $slot }}
    @stack('scripts')
</body>
</html>
