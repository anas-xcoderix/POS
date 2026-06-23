@php $title = 'Dashboard'; @endphp
<x-erp-layout>
    {{-- KPI row --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-12">
        {{-- Total Revenue --}}
        <div class="erp-stat-card lg:col-span-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['total_revenue'], 0) }}</p>
                    <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-emerald-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $stats['revenue_growth'] }}%
                    </span>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="absolute bottom-0 right-0 h-16 w-24 opacity-20">
                <svg viewBox="0 0 100 40" class="h-full w-full text-blue-400" preserveAspectRatio="none"><path fill="currentColor" d="M0 35 Q25 10 50 25 T100 15 V40 H0Z"/></svg>
            </div>
        </div>

        {{-- Total Sales --}}
        <div class="erp-stat-card lg:col-span-3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Sales Invoices</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['sales_invoices']) }}</p>
                    <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-emerald-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $stats['orders_growth'] }}%
                    </span>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                </div>
            </div>
        </div>

        {{-- Top Customers featured card --}}
        <div class="erp-gradient-feature lg:col-span-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Top Customers</h3>
                <a href="{{ route('customers.index') }}" class="text-sm font-medium text-white/90 hover:text-white">View All</a>
            </div>

            @if($topCustomers->isNotEmpty())
                <div class="mt-4 flex -space-x-2">
                    @foreach($topCustomers->take(4) as $customer)
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-white/20 text-xs font-bold backdrop-blur-sm">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endforeach
                </div>
                @php $featured = $topCustomers->first(); @endphp
                <div class="mt-5">
                    <p class="text-xl font-bold">{{ $featured->name }}</p>
                    <p class="mt-1 text-sm text-white/80">
                        {{ number_format($featured->total_spent ?? 0, 2) }}
                        <span class="mx-1">•</span>
                        {{ $featured->orders_count ?? 0 }} Orders
                    </p>
                </div>
                <div class="mt-4">
                    <span class="inline-flex rounded-full bg-white/25 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
                        {{ $stats['customers'] }} Total Customers
                    </span>
                </div>
            @else
                <p class="mt-6 text-sm text-white/80">No customer sales yet. Create a sales invoice to see top customers here.</p>
                <a href="{{ route('sales-invoices.create') }}" class="mt-4 inline-flex rounded-full bg-white/25 px-4 py-2 text-sm font-semibold backdrop-blur-sm hover:bg-white/35">Create Sale</a>
            @endif
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-bold text-slate-900">Quick Links</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Sales Invoices', 'document', route('sales-invoices.index')],
                ['Parts Master', 'box', route('parts.index')],
                ['Purchase Orders', 'cart', route('purchase-orders.index')],
                ['Customers', 'users', route('customers.index')],
            ] as [$label, $icon, $url])
                <a href="{{ $url }}" class="erp-quick-link group">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                            <x-ui.icon :name="$icon" class="h-5 w-5 text-slate-500" />
                        </div>
                        <span class="font-semibold text-slate-700">{{ $label }}</span>
                    </div>
                    <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        {{-- Sales Statistics --}}
        <div class="erp-card p-6 xl:col-span-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-slate-900">Sales Statistics</h3>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-cyan-500"></span> Posted</span>
                    <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-amber-300"></span> Draft</span>
                    <span class="erp-badge-slate">{{ $stats['posted_sales'] }} Total Posted</span>
                </div>
            </div>
            <div class="flex h-56 items-end justify-between gap-2 sm:gap-4">
                @foreach($weeklySales as $day)
                    @php
                        $total = $day['completed'] + $day['pending'];
                        $height = $total > 0 ? max(12, ($total / $weeklyMax) * 100) : 4;
                        $pct = $weeklyMax > 0 ? round(($day['completed'] / $weeklyMax) * 100) : 0;
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2">
                        @if($day['completed'] > 0)
                            <span class="text-xs font-semibold text-slate-500">{{ $pct }}%</span>
                        @else
                            <span class="text-xs text-transparent">0</span>
                        @endif
                        <div class="flex w-full max-w-[48px] flex-col justify-end rounded-t-2xl bg-slate-100" style="height: 180px">
                            @if($day['pending'] > 0)
                                <div class="w-full rounded-t-2xl bg-amber-300" style="height: {{ max(4, ($day['pending'] / $weeklyMax) * 100) }}%"></div>
                            @endif
                            <div class="w-full rounded-t-2xl bg-gradient-to-t from-cyan-500 to-cyan-400" style="height: {{ $day['completed'] > 0 ? max(8, ($day['completed'] / $weeklyMax) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-slate-500">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Revenue chart --}}
        <div class="erp-card p-6 xl:col-span-4">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Today's Revenue</h3>
                <span class="erp-badge-cyan">Live</span>
            </div>
            <canvas id="revenueChart" height="200" class="w-full"></canvas>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="erp-card overflow-hidden">
            <div class="border-b border-slate-50 px-6 py-4">
                <h3 class="font-bold text-slate-900">Recent Sales</h3>
            </div>
            @forelse($recentSales as $sale)
                <div class="flex items-center justify-between border-t border-slate-50 px-6 py-4 first:border-t-0">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $sale->invoice_no }}</p>
                        <p class="text-sm text-slate-500">{{ $sale->customer?->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-900">{{ number_format($sale->total_amount, 2) }}</p>
                        <span class="erp-badge {{ $sale->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ ucfirst($sale->status) }}</span>
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="No sales yet" description="Your recent sales will appear here." />
            @endforelse
        </div>

        <div class="erp-card overflow-hidden">
            <div class="border-b border-slate-50 px-6 py-4">
                <h3 class="font-bold text-slate-900">Recent Purchases</h3>
            </div>
            @forelse($recentPurchases as $purchase)
                <div class="flex items-center justify-between border-t border-slate-50 px-6 py-4 first:border-t-0">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $purchase->invoice_no }}</p>
                        <p class="text-sm text-slate-500">{{ $purchase->vendor?->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-900">{{ number_format($purchase->total_amount, 2) }}</p>
                        <span class="erp-badge {{ $purchase->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ ucfirst($purchase->status) }}</span>
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="No purchases yet" description="Your recent purchases will appear here." />
            @endforelse
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
