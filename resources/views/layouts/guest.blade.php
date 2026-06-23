<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ __('auth.sign_in') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @if(is_rtl())
        <link href="https://fonts.bunny.net/css?family=noto-sans-arabic:400,500,600,700&display=swap" rel="stylesheet" />
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 {{ is_rtl() ? 'font-arabic' : '' }}">
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="absolute top-4 end-4">
            <form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-orange-600">
                    {{ app()->getLocale() === 'ar' ? __('ui.language_en') : __('ui.language_ar') }}
                </button>
            </form>
        </div>
        <div class="w-full max-w-md">
            <div class="erp-card p-8 shadow-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
