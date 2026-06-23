@php $title = 'Dashboard'; @endphp
<x-erp-layout>
    {{-- Page header row --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Overview of your business performance</p>
        </div>
        <div class="erp-pill w-full sm:w-auto justify-center">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ $dateRange }}
        </div>
    </div>

    {{-- Hero + Featured --}}
    <div class="mb-6 grid grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="erp-hero-card xl:col-span-7">
            <div class="relative z-10 max-w-lg">
                <p class="text-sm font-medium text-slate-500">Welcome back,</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">{{ auth()->user()->name }}</h2>
                <p class="mt-3 text-sm text-slate-600">
                    Total Parts: <strong class="text-slate-900">{{ number_format($stats['parts']) }}</strong>
                    <span class="mx-2 text-slate-300">|</span>
                    Stock Lines: <strong class="text-orange-600">{{ number_format($stats['stock_lines']) }}</strong>
                    <span class="mx-2 text-slate-300">|</span>
                    Customers: <strong class="text-slate-900">{{ number_format($stats['customers']) }}</strong>
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('sales-invoices.index') }}" class="erp-btn-primary">Sales</a>
                    <a href="{{ route('parts.index') }}" class="erp-btn-dark">
                        <x-ui.icon name="plus" class="h-4 w-4" /> Add Part
                    </a>
                </div>
            </div>
            <div class="pointer-events-none absolute bottom-0 right-0 hidden h-full w-1/2 opacity-90 sm:block">
                <div class="flex h-full items-end justify-end p-6">
                    <div class="rounded-2xl bg-gradient-to-br from-orange-100 to-amber-50 p-6 ring-1 ring-orange-100">
                        <div class="flex h-24 w-24 items-center justify-center rounded-xl bg-white shadow-sm">
                            <x-ui.icon name="box" class="h-12 w-12 text-orange-500" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="erp-feature-card xl:col-span-5">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900">Top Customer</h3>
                    <a href="{{ route('customers.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">View All</a>
                </div>
            </div>
            @if($topCustomers->isNotEmpty())
                @php $featured = $topCustomers->first(); @endphp
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-lg font-bold text-orange-600">
                            {{ strtoupper(substr($featured->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900">{{ $featured->name }}</p>
                            <p class="mt-1 text-2xl font-bold text-orange-600">{{ number_format($featured->total_spent ?? 0, 2) }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <p class="text-slate-500">Orders</p>
                                    <p class="font-semibold text-slate-800">{{ $featured->orders_count ?? 0 }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <p class="text-slate-500">Type</p>
                                    <p class="font-semibold text-slate-800">{{ ucfirst($featured->customer_type ?? 'retail') }}</p>
                                </div>
                                <div class="col-span-2 rounded-lg bg-slate-50 px-3 py-2 sm:col-span-1">
                                    <p class="text-slate-500">Total Customers</p>
                                    <p class="font-semibold text-slate-800">{{ $stats['customers'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-sm text-slate-500">
                    <p>No customer sales yet.</p>
                    <a href="{{ route('sales-invoices.create') }}" class="mt-3 inline-flex text-orange-600 font-medium">Create first sale →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="erp-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['total_revenue'], 0) }}</p>
                    <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold {{ $stats['revenue_growth'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $stats['revenue_growth'] >= 0 ? '+' : '' }}{{ $stats['revenue_growth'] }}% Last Month
                    </span>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50">
                    <svg class="h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex h-8 items-end gap-1">
                @foreach([40,65,45,80,55,70,90,60,75,50,85,68] as $h)
                    <div class="flex-1 rounded-sm bg-teal-200" style="height: {{ $h }}%"></div>
                @endforeach
            </div>
        </div>

        <div class="erp-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Sales Invoices</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['sales_invoices']) }}</p>
                    <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold {{ $stats['orders_growth'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $stats['orders_growth'] >= 0 ? '+' : '' }}{{ $stats['orders_growth'] }}% Last Month
                    </span>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50">
                    <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex h-8 items-end gap-1">
                @foreach([55,70,45,90,60,75,50,80,65,85,40,72] as $h)
                    <div class="flex-1 rounded-sm bg-orange-200" style="height: {{ $h }}%"></div>
                @endforeach
            </div>
        </div>

        <div class="erp-stat-card sm:col-span-2 xl:col-span-1">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Purchase Orders</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['purchase_orders']) }}</p>
                    <span class="mt-2 text-sm text-slate-500">{{ number_format($stats['purchase_invoices']) }} invoices</span>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50">
                    <svg class="h-6 w-6 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex h-8 items-end gap-1">
                @foreach([30,50,70,40,60,80,55,75,45,65,85,50] as $h)
                    <div class="flex-1 rounded-sm bg-violet-200" style="height: {{ $h }}%"></div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="mb-6">
        <h3 class="mb-3 text-base font-bold text-slate-900">Quick Links</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Sales Invoices', 'document', route('sales-invoices.index')],
                ['Parts Master', 'box', route('parts.index')],
                ['Purchase Orders', 'cart', route('purchase-orders.index')],
                ['Customers', 'users', route('customers.index')],
            ] as [$label, $icon, $url])
                <a href="{{ $url }}" class="erp-quick-link group">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                            <x-ui.icon :name="$icon" class="h-5 w-5 text-orange-500" />
                        </div>
                        <span class="truncate font-semibold text-slate-700">{{ $label }}</span>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        <div class="erp-card p-5 sm:p-6 xl:col-span-8">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900 sm:text-lg">Weekly Sales</h3>
                <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm">
                    <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span> Posted</span>
                    <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span> Draft</span>
                </div>
            </div>
            <div class="flex h-48 items-end justify-between gap-1 sm:h-56 sm:gap-2">
                @foreach($weeklySales as $day)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="flex w-full max-w-[40px] flex-col justify-end rounded-t-md bg-slate-100 sm:max-w-[48px]" style="height: 160px">
                            @if($day['pending'] > 0)
                                <div class="w-full rounded-t-md bg-amber-300" style="height: {{ max(4, ($day['pending'] / $weeklyMax) * 100) }}%"></div>
                            @endif
                            <div class="w-full rounded-t-md bg-orange-500" style="height: {{ $day['completed'] > 0 ? max(8, ($day['completed'] / $weeklyMax) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-[10px] font-medium text-slate-500 sm:text-xs">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="erp-card p-5 sm:p-6 xl:col-span-4">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 sm:text-lg">Today's Revenue</h3>
                <span class="erp-badge-orange">Live</span>
            </div>
            <div class="h-48 sm:h-52">
                <canvas id="revenueChart" class="h-full w-full"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="erp-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Recent Sales</h3>
                <a href="{{ route('sales-invoices.index') }}" class="text-sm text-orange-600 hover:text-orange-700">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentSales as $sale)
                    <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $sale->invoice_no }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $sale->customer?->name }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end">
                            <p class="font-bold text-slate-900">{{ number_format($sale->total_amount, 2) }}</p>
                            <span class="erp-badge {{ $sale->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ ucfirst($sale->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6"><x-ui.empty-state title="No sales yet" description="Your recent sales will appear here." /></div>
                @endforelse
            </div>
        </div>

        <div class="erp-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Recent Purchases</h3>
                <a href="{{ route('purchase-invoices.index') }}" class="text-sm text-orange-600 hover:text-orange-700">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentPurchases as $purchase)
                    <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $purchase->invoice_no }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $purchase->vendor?->name }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end">
                            <p class="font-bold text-slate-900">{{ number_format($purchase->total_amount, 2) }}</p>
                            <span class="erp-badge {{ $purchase->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ ucfirst($purchase->status) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6"><x-ui.empty-state title="No purchases yet" description="Your recent purchases will appear here." /></div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.initRevenueChart) {
                window.initRevenueChart(
                    @json($hourlyRevenue->pluck('label')),
                    @json($hourlyRevenue->pluck('amount'))
                );
            }
        });
    </script>
    @endpush
</x-erp-layout>
