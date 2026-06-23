<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Part;
use App\Models\SalesInvoice;
use App\Services\BranchScopeService;
use App\Services\DocumentEditService;
use App\Services\GranularPermissionService;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesInvoiceController extends Controller
{
    public function __construct(
        private SalesService $salesService,
        private BranchScopeService $branchScope,
        private DocumentEditService $documentEditService,
        private GranularPermissionService $granularPermissions,
    ) {}

    public function index(Request $request): View
    {
        $query = SalesInvoice::query()->with(['customer', 'branch']);
        $this->branchScope->apply($query);

        if ($search = $request->get('search')) {
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        return view('sales.invoices.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'canEditPosted' => $this->granularPermissions->can(auth()->user(), 'sales.edit_posted'),
        ]);
    }

    public function create(): View
    {
        $defaultBranch = $this->branchScope->defaultBranchId();

        return view('sales.invoices.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'defaultBranchId' => $defaultBranch,
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
            'invoice_type' => 'required|string|in:cash,credit',
            'status' => 'required|string',
            'paid_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'nullable|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.manual_price' => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['paid_amount'] = $data['paid_amount'] ?? 0;
        $postStock = $data['status'] === 'posted';

        try {
            $this->salesService->createInvoice($data, $data['items'], $postStock);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-invoices.index')->with('success', __('messages.sales.invoice_created'));
    }

    public function post(SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->salesService->postInvoice($salesInvoice, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.sales.invoice_posted'));
    }

    public function void(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        $data = $request->validate(['void_reason' => 'required|string|max:500']);

        try {
            $this->salesService->voidInvoice($salesInvoice, $data['void_reason'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.sales.invoice_voided'));
    }

    public function editPosted(SalesInvoice $salesInvoice): View
    {
        $this->granularPermissions->assert(auth()->user(), 'sales.edit_posted');

        $salesInvoice->load(['items.part', 'items.location', 'customer', 'branch']);

        return view('sales.invoices.edit-posted', [
            'invoice' => $salesInvoice,
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
        ]);
    }

    public function updatePosted(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        $data = $request->validate([
            'invoice_date' => 'required|date',
            'invoice_type' => 'required|string|in:cash,credit',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'nullable|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vat_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $this->documentEditService->updatePostedSalesInvoice(
                auth()->user(),
                $salesInvoice,
                $data,
                $data['items']
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales-invoices.index')->with('success', __('messages.sales.invoice_updated'));
    }
}
