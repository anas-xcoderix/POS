<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Part;
use App\Models\SalesInvoice;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    public function __construct(private SalesService $salesService) {}

    public function index(Request $request): View
    {
        $query = SalesInvoice::query()->with(['customer', 'branch']);

        if ($search = $request->get('search')) {
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        return view('sales.invoices.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('sales.invoices.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'invoiceNo' => 'SI-'.now()->format('Ymd').'-'.str_pad((string) (SalesInvoice::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:sales_invoices,invoice_no',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|string',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'nullable|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['created_by'] = auth()->id();
        $postStock = $data['status'] === 'posted';

        $this->salesService->createInvoice($data, $data['items'], $postStock);

        return redirect()->route('sales-invoices.index')->with('success', 'Sales invoice created.');
    }

    public function post(SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->salesService->postInvoice($salesInvoice, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Invoice posted and stock updated.');
    }
}
