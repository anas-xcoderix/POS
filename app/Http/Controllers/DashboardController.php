<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Part;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\StockBalance;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalRevenue = (float) SalesInvoice::where('status', 'posted')->sum('total_amount');
        $totalSales = SalesInvoice::count();
        $postedSales = SalesInvoice::where('status', 'posted')->count();

        $thisMonthRevenue = (float) SalesInvoice::where('status', 'posted')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('total_amount');

        $lastMonthRevenue = (float) SalesInvoice::where('status', 'posted')
            ->whereMonth('invoice_date', now()->subMonth()->month)
            ->whereYear('invoice_date', now()->subMonth()->year)
            ->sum('total_amount');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        $thisMonthOrders = SalesInvoice::whereMonth('invoice_date', now()->month)->count();
        $lastMonthOrders = SalesInvoice::whereMonth('invoice_date', now()->subMonth()->month)->count();
        $ordersGrowth = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100)
            : ($thisMonthOrders > 0 ? 100 : 0);

        $topCustomers = Customer::query()
            ->withSum(['salesInvoices as total_spent' => fn ($q) => $q->where('status', 'posted')], 'total_amount')
            ->withCount(['salesInvoices as orders_count' => fn ($q) => $q->where('status', 'posted')])
            ->whereHas('salesInvoices', fn ($q) => $q->where('status', 'posted'))
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        $weeklySales = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'completed' => SalesInvoice::whereDate('invoice_date', $date)->where('status', 'posted')->count(),
                'pending' => SalesInvoice::whereDate('invoice_date', $date)->where('status', 'draft')->count(),
            ];
        });

        $weeklyMax = max(1, $weeklySales->max(fn ($d) => $d['completed'] + $d['pending']));

        $hourlyRevenue = collect(range(8, 20, 2))->map(function ($hour) {
            $amount = (float) SalesInvoice::where('status', 'posted')
                ->whereDate('invoice_date', today())
                ->whereRaw('HOUR(created_at) >= ? AND HOUR(created_at) < ?', [$hour, $hour + 2])
                ->sum('total_amount');

            return [
                'label' => sprintf('%02d:00', $hour),
                'amount' => $amount,
            ];
        });

        return view('dashboard', [
            'stats' => [
                'parts' => Part::count(),
                'customers' => Customer::count(),
                'vendors' => Vendor::count(),
                'sales_invoices' => $totalSales,
                'purchase_orders' => PurchaseOrder::count(),
                'purchase_invoices' => PurchaseInvoice::count(),
                'stock_lines' => StockBalance::where('quantity', '>', 0)->count(),
                'total_revenue' => $totalRevenue,
                'posted_sales' => $postedSales,
                'revenue_growth' => $revenueGrowth,
                'orders_growth' => $ordersGrowth,
            ],
            'recentSales' => SalesInvoice::with('customer')->latest()->take(5)->get(),
            'recentPurchases' => PurchaseInvoice::with('vendor')->latest()->take(5)->get(),
            'topCustomers' => $topCustomers,
            'weeklySales' => $weeklySales,
            'weeklyMax' => $weeklyMax,
            'hourlyRevenue' => $hourlyRevenue,
            'dateRange' => now()->subDays(29)->format('d M y').' - '.now()->format('d M y'),
        ]);
    }
}
