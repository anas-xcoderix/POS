<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @isset($title) — {{ $title }} @endisset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|noto-sans-arabic:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="font-sans bg-gray-50 {{ $isRtl ? 'erp-rtl' : 'erp-ltr' }}" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" @close-sidebar.window="sidebarOpen = false">

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

<div class="flex min-h-screen">
    <aside class="erp-sidebar fixed inset-y-0 z-50 flex w-[270px] max-w-[85vw] flex-col bg-white transition-transform duration-300 lg:static lg:max-w-none lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : ($isRtl ? 'translate-x-full lg:translate-x-0' : '-translate-x-full lg:translate-x-0')">

        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-500 text-sm font-bold text-white shadow-sm">PF</div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-slate-900">{{ config('app.name', 'PartFlow') }}</p>
                <p class="truncate text-xs text-slate-500">{{ __('ui.app_tagline') }}</p>
            </div>
            <button type="button" class="erp-sidebar-close rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = false">
                <x-ui.icon name="x" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-2">
            <x-ui.sidebar-section :title="__('nav.main')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="dashboard" icon="dashboard" :label="__('nav.dashboard')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.inventory')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="parts.index" icon="box" :label="__('nav.parts_master')" />
                <x-ui.sidebar-link route="stock.index" icon="box" :label="__('nav.stock')" />
                <x-ui.sidebar-link route="stock-batches.index" icon="box" :label="__('nav.stock_batches')" />
                <x-ui.sidebar-link route="showroom-vehicles.index" icon="truck" :label="__('nav.showroom_vehicles')" />
                <x-ui.sidebar-link route="stock.movements" icon="document" :label="__('nav.movements')" />
                <x-ui.sidebar-link route="stock-transfers.index" icon="truck" :label="__('nav.transfers')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.sales')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="quotations.index" icon="document" :label="__('nav.quotations')" />
                <x-ui.sidebar-link route="proforma-invoices.index" icon="document" :label="__('nav.proforma')" />
                <x-ui.sidebar-link route="sales-invoices.index" icon="document" :label="__('nav.sales_invoices')" />
                <x-ui.sidebar-link route="sale-returns.index" icon="document" :label="__('nav.sale_returns')" />
                <x-ui.sidebar-link route="delivery-notes.index" icon="truck" :label="__('nav.delivery_notes')" />
                <x-ui.sidebar-link route="pick-tickets.index" icon="document" :label="__('nav.pick_tickets')" />
                <x-ui.sidebar-link route="pos.index" icon="cart" :label="__('nav.pos')" />
                <x-ui.sidebar-link route="customers.index" icon="users" :label="__('nav.customers')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.purchase')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="purchase-orders.index" icon="cart" :label="__('nav.purchase_orders')" />
                <x-ui.sidebar-link route="purchase-invoices.index" icon="document" :label="__('nav.purchase_invoices')" />
                <x-ui.sidebar-link route="purchase-returns.index" icon="document" :label="__('nav.purchase_returns')" />
                <x-ui.sidebar-link route="vendors.index" icon="truck" :label="__('nav.vendors')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.inventory_ops')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="stock-counts.index" icon="box" :label="__('nav.physical_count')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.reports')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="reports.index" icon="document" :label="__('nav.reports_center')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.finance')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="accounts.index" icon="document" :label="__('nav.chart_of_accounts')" />
                <x-ui.sidebar-link route="journal-entries.index" icon="document" :label="__('nav.journal_entries')" />
                <x-ui.sidebar-link route="payments.index" icon="document" :label="__('nav.payments')" />
                <x-ui.sidebar-link route="cash-book.index" icon="document" :label="__('nav.cash_book')" />
                <x-ui.sidebar-link route="cheques.index" icon="document" :label="__('nav.cheques')" />
                <x-ui.sidebar-link route="fixed-assets.index" icon="building" :label="__('nav.fixed_assets')" />
                <x-ui.sidebar-link route="currencies.index" icon="globe" :label="__('nav.currencies')" />
                <x-ui.sidebar-link route="finance.periods.index" icon="document" :label="__('nav.fiscal_periods')" />
                <x-ui.sidebar-link route="finance.reports.index" icon="document" :label="__('nav.finance_reports')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.workshop')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="job-cards.index" icon="truck" :label="__('nav.job_cards')" />
                <x-ui.sidebar-link route="vehicles.index" icon="truck" :label="__('nav.vehicles')" />
                <x-ui.sidebar-link route="vehicle-orders.index" icon="document" :label="__('nav.vehicle_orders')" />
                <x-ui.sidebar-link route="workshop.reports.wip" icon="document" :label="__('nav.wip_report')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.hr')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="employees.index" icon="users" :label="__('nav.employees')" />
                <x-ui.sidebar-link route="departments.index" icon="building" :label="__('nav.departments')" />
                <x-ui.sidebar-link route="attendance.index" icon="document" :label="__('nav.attendance')" />
                <x-ui.sidebar-link route="payroll.index" icon="document" :label="__('nav.payroll')" />
                <x-ui.sidebar-link route="hr.reports.expiring-documents" icon="document" :label="__('nav.expiring_docs')" />
            </div>

            <x-ui.sidebar-section :title="__('nav.masters')" />
            <div class="space-y-0.5">
                <x-ui.sidebar-link route="branches.index" icon="building" :label="__('nav.branches')" />
                <x-ui.sidebar-link route="locations.index" icon="map-pin" :label="__('nav.locations')" />
                <x-ui.sidebar-link route="brands.index" icon="tag" :label="__('nav.brands')" />
                <x-ui.sidebar-link route="origins.index" icon="globe" :label="__('nav.origins')" />
                <x-ui.sidebar-link route="franchises.index" icon="globe" :label="__('nav.franchises')" />
                <x-ui.sidebar-link route="units.index" icon="tag" :label="__('nav.units')" />
            </div>

            @if(auth()->user()?->role === 'admin')
                <x-ui.sidebar-section :title="__('nav.system')" />
                <div class="space-y-0.5">
                    <x-ui.sidebar-link route="settings.index" icon="cog" :label="__('nav.settings')" />
                    <x-ui.sidebar-link route="users.index" icon="users" :label="__('nav.users_roles')" />
                    <x-ui.sidebar-link route="audit-logs.index" icon="document" :label="__('nav.audit_logs')" />
                </div>
            @endif
        </nav>

        <div class="border-t border-slate-100 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="erp-sidebar-link-inactive w-full">
                    <x-ui.icon name="logout" class="h-5 w-5 text-slate-400" />
                    <span>{{ __('nav.sign_out') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button type="button" class="shrink-0 rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden" @click="sidebarOpen = true">
                        <x-ui.icon name="menu" class="h-5 w-5" />
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-bold text-slate-900 sm:text-xl">{{ $title ?? __('nav.dashboard') }}</h1>
                        <p class="hidden text-xs text-slate-500 sm:block">
                            {{ __('ui.home') }} <span class="mx-1 text-slate-300">/</span> {{ $title ?? __('nav.dashboard') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <form method="POST" action="{{ route('locale.switch', $isRtl ? 'en' : 'ar') }}" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-ghost !py-2 !px-3 text-xs" title="{{ __('ui.switch_language') }}">
                            {{ $isRtl ? __('ui.language_en') : __('ui.language_ar') }}
                        </button>
                    </form>
                    <a href="{{ route('sales-invoices.create') }}" class="erp-btn-primary !py-2 !px-3 text-xs sm:text-sm">
                        <x-ui.icon name="plus" class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ __('ui.new_sale') }}</span>
                        <span class="sm:hidden">{{ __('ui.sale') }}</span>
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
