<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @isset($title) — {{ $title }} @endisset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="font-sans bg-[#f4f6f8]" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

<div class="flex min-h-screen">
    {{-- Icon sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-[76px] flex-col border-r border-slate-800/50 bg-[#1a1d21] transition-transform duration-300 lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="flex h-16 items-center justify-center border-b border-slate-800/80">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-teal-500 text-sm font-bold text-white shadow-lg shadow-cyan-500/30">
                IA
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <x-ui.nav-item route="dashboard" icon="dashboard" label="Dashboard" />
            <x-ui.nav-item route="parts.index" icon="box" label="Parts" />
            <x-ui.nav-item route="stock.index" icon="box" label="Stock" />
            <x-ui.nav-item route="stock.movements" icon="document" label="Movements" />
            <x-ui.nav-item route="quotations.index" icon="document" label="Quotes" />
            <x-ui.nav-item route="sales-invoices.index" icon="document" label="Sales" />
            <x-ui.nav-item route="sale-returns.index" icon="document" label="Returns" />
            <x-ui.nav-item route="stock-transfers.index" icon="truck" label="Transfers" />
            <x-ui.nav-item route="purchase-orders.index" icon="cart" label="Purchase" />
            <x-ui.nav-item route="customers.index" icon="users" label="Customers" />
            <x-ui.nav-item route="vendors.index" icon="truck" label="Vendors" />
            <x-ui.nav-item route="branches.index" icon="building" label="Branches" />
            <x-ui.nav-item route="locations.index" icon="map-pin" label="Locations" />
        </nav>

        <div class="border-t border-slate-800/80 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sign out" class="erp-sidebar-icon-inactive mx-auto w-full">
                    <x-ui.icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-0">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/60 bg-[#f4f6f8]/90 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button type="button" class="erp-btn-secondary !rounded-xl !p-2.5 lg:hidden" @click="sidebarOpen = true">
                        <x-ui.icon name="menu" class="h-5 w-5" />
                    </button>
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-xl font-bold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                            @if(request()->routeIs('dashboard'))
                                <span class="erp-badge-cyan">Sales: {{ \App\Models\SalesInvoice::count() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    @if(request()->routeIs('dashboard'))
                        <span class="erp-pill hidden sm:inline-flex">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ now()->subDays(29)->format('d M y') }} - {{ now()->format('d M y') }}
                        </span>
                    @endif

                    <div class="flex items-center gap-2">
                        <div class="hidden h-9 w-9 items-center justify-center rounded-full bg-white text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-100 sm:flex">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden h-9 w-9 overflow-hidden rounded-full bg-gradient-to-br from-violet-400 to-fuchsia-400 sm:block"></div>
                    </div>
                </div>
            </div>

            @if(request()->routeIs('dashboard'))
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/50 px-4 py-3 sm:px-6 lg:px-8">
                    <span class="erp-pill sm:hidden">{{ $dateRange ?? now()->format('d M y') }}</span>
                    <div class="flex flex-wrap gap-2 sm:ml-auto">
                        <button type="button" class="erp-btn-dark text-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export
                        </button>
                        <a href="{{ route('sales-invoices.create') }}" class="erp-btn-primary text-sm">
                            <x-ui.icon name="plus" class="h-4 w-4" /> New Sale
                        </a>
                    </div>
                </div>
            @endif

            @isset($headerAction)
                <div class="border-t border-slate-200/50 px-4 py-3 sm:px-6 lg:px-8">{{ $headerAction }}</div>
            @endisset
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if(session('success'))
                <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
