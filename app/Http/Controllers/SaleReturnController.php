<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Part;
use App\Models\SaleReturn;
use App\Models\SalesInvoice;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct(private SalesService $salesService) {}

    public function index(Request $request): View
    {
        $query = SaleReturn::with(['customer', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('return_no', 'like', "%{$search}%");
        }

        return view('sale-returns.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $salesInvoice = $request->filled('sales_invoice_id')
            ? SalesInvoice::with('items.part')->find($request->sales_invoice_id)
            : null;

        return view('sale-returns.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'salesInvoices' => SalesInvoice::where('status', 'posted')->with('customer')->latest()->take(50)->get(),
            'salesInvoice' => $salesInvoice,
            'returnNo' => 'SR-'.now()->format('Ymd').'-'.str_pad((string) (SaleReturn::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'return_no' => 'required|string|unique:sale_returns,return_no',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'return_date' => 'required|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['created_by'] = auth()->id();
        $postStock = $data['status'] === 'posted';

        try {
            $this->salesService->createSaleReturn($data, $data['items'], $postStock);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-returns.index')->with('success', 'Sale return created.');
    }

    public function post(SaleReturn $saleReturn): RedirectResponse
    {
        try {
            $this->salesService->postSaleReturn($saleReturn, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return posted and stock restored.');
    }
}
