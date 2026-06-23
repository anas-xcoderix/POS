<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @isset($title) — {{ $title }} @endisset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="font-sans bg-gray-50" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" @close-sidebar.window="sidebarOpen = false">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-[270px] flex-col border-r border-slate-200 bg-white transition-transform duration-300 lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500 text-sm font-bold text-white shadow-sm">
                PF
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900">{{ config('app.name', 'PartFlow') }}</p>
                <p class="truncate text-xs text-slate-500">Parts & Operations</p>
            </div>
            <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-2">
            <x-ui.sidebar-section title="Main" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="dashboard" icon="dashboard" label="Dashboard" />
            </div>

            <x-ui.sidebar-section title="Inventory" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="parts.index" icon="box" label="Parts Master" />
                <x-ui.sidebar-link route="stock.index" icon="box" label="Stock" />
                <x-ui.sidebar-link route="stock.movements" icon="document" label="Movements" />
                <x-ui.sidebar-link route="stock-transfers.index" icon="truck" label="Transfers" />
            </div>

            <x-ui.sidebar-section title="Sales" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="quotations.index" icon="document" label="Quotations" />
                <x-ui.sidebar-link route="sales-invoices.index" icon="document" label="Sales Invoices" />
                <x-ui.sidebar-link route="sale-returns.index" icon="document" label="Sale Returns" />
                <x-ui.sidebar-link route="customers.index" icon="users" label="Customers" />
            </div>

            <x-ui.sidebar-section title="Purchase" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="purchase-orders.index" icon="cart" label="Purchase Orders" />
                <x-ui.sidebar-link route="purchase-invoices.index" icon="document" label="Purchase Invoices" />
                <x-ui.sidebar-link route="vendors.index" icon="truck" label="Vendors" />
            </div>

            <x-ui.sidebar-section title="Reports" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="reports.index" icon="document" label="Reports Center" />
            </div>

            <x-ui.sidebar-section title="Finance" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="accounts.index" icon="document" label="Chart of Accounts" />
                <x-ui.sidebar-link route="journal-entries.index" icon="document" label="Journal Entries" />
                <x-ui.sidebar-link route="finance.reports.index" icon="document" label="Reports" />
            </div>

            <x-ui.sidebar-section title="Workshop" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="job-cards.index" icon="truck" label="Job Cards" />
                <x-ui.sidebar-link route="vehicles.index" icon="truck" label="Vehicles" />
                <x-ui.sidebar-link route="workshop.reports.wip" icon="document" label="WIP Report" />
            </div>

            <x-ui.sidebar-section title="HR" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="employees.index" icon="users" label="Employees" />
                <x-ui.sidebar-link route="departments.index" icon="building" label="Departments" />
                <x-ui.sidebar-link route="attendance.index" icon="document" label="Attendance" />
                <x-ui.sidebar-link route="payroll.index" icon="document" label="Payroll" />
                <x-ui.sidebar-link route="hr.reports.expiring-documents" icon="document" label="Expiring Docs" />
            </div>

            <x-ui.sidebar-section title="Masters" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="branches.index" icon="building" label="Branches" />
                <x-ui.sidebar-link route="locations.index" icon="map-pin" label="Locations" />
                <x-ui.sidebar-link route="brands.index" icon="tag" label="Brands" />
                <x-ui.sidebar-link route="origins.index" icon="globe" label="Origins" />
                <x-ui.sidebar-link route="franchises.index" icon="globe" label="Franchises" />
            </div>

            @if(auth()->user()?->role === 'admin')
                <x-ui.sidebar-section title="System" />
                <div class="space-y-0.5">
                    <x-ui.sidebar-link route="settings.index" icon="cog" label="Settings" />
                    <x-ui.sidebar-link route="users.index" icon="users" label="Users & Roles" />
                </div>
            @endif
        </nav>

        <div class="border-t border-slate-100 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="erp-sidebar-link-inactive w-full">
                    <x-ui.icon name="logout" class="h-5 w-5 text-slate-400" />
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden" @click="sidebarOpen = true">
                        <x-ui.icon name="menu" class="h-5 w-5" />
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold text-slate-900 sm:text-xl">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">
                            Home <span class="mx-1 text-slate-300">/</span> {{ $title ?? 'Dashboard' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <a href="{{ route('sales-invoices.create') }}" class="erp-btn-primary !py-2 !px-3 text-xs sm:text-sm">
                        <x-ui.icon name="plus" class="h-4 w-4" />
                        <span class="hidden sm:inline">New Sale</span>
                        <span class="sm:hidden">Sale</span>
                    </a>

                    <div class="hidden items-center gap-2 sm:flex">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 text-sm font-bold text-orange-600">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @isset($headerAction)
                <div class="border-t border-slate-100 px-4 py-3 sm:px-6 lg:px-8">{{ $headerAction }}</div>
            @endisset
        </header>

        <main class="flex-1 px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
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
