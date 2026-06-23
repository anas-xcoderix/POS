<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\PurchaseInvoice;
use App\Models\Vendor;
use App\Services\DocumentEditService;
use App\Services\GranularPermissionService;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        private PurchaseService $purchaseService,
        private DocumentEditService $documentEditService,
        private GranularPermissionService $granularPermissions,
    ) {}

    public function index(Request $request): View
    {
        $query = PurchaseInvoice::query()->with(['vendor', 'branch']);

        if ($search = $request->get('search')) {
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        return view('purchases.invoices.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'canEditPosted' => $this->granularPermissions->can(auth()->user(), 'purchase.edit_posted'),
        ]);
    }

    public function create(): View
    {
        return view('purchases.invoices.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'vendors' => Vendor::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'invoiceNo' => 'PI-'.now()->format('Ymd').'-'.str_pad((string) (PurchaseInvoice::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:purchase_invoices,invoice_no',
            'branch_id' => 'required|exists:branches,id',
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_date' => 'required|date',
            'vendor_invoice_no' => 'nullable|string',
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

        $this->purchaseService->createPurchaseInvoice($data, $data['items'], $postStock);

        return redirect()->route('purchase-invoices.index')->with('success', 'Purchase invoice created.');
    }

    public function post(PurchaseInvoice $purchaseInvoice): RedirectResponse
    {
        try {
            $this->purchaseService->postInvoice($purchaseInvoice, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase invoice posted and stock received.');
    }

    public function void(Request $request, PurchaseInvoice $purchaseInvoice): RedirectResponse
    {
        $data = $request->validate(['void_reason' => 'required|string|max:500']);

        try {
            $this->purchaseService->voidInvoice($purchaseInvoice, $data['void_reason'], auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase invoice voided.');
    }

    public function editPosted(PurchaseInvoice $purchaseInvoice): View
    {
        $this->granularPermissions->assert(auth()->user(), 'purchase.edit_posted');

        $purchaseInvoice->load(['items.part', 'items.location', 'vendor', 'branch']);

        return view('purchases.invoices.edit-posted', [
            'invoice' => $purchaseInvoice,
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
        ]);
    }

    public function updatePosted(Request $request, PurchaseInvoice $purchaseInvoice): RedirectResponse
    {
        $data = $request->validate([
            'invoice_date' => 'required|date',
            'vendor_invoice_no' => 'nullable|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'nullable|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $this->documentEditService->updatePostedPurchaseInvoice(
                auth()->user(),
                $purchaseInvoice,
                $data,
                $data['items']
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-invoices.index')->with('success', 'Posted purchase invoice updated.');
    }
}
