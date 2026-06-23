<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function index(Request $request): View
    {
        $query = PurchaseOrder::query()->with(['vendor', 'branch']);

        if ($search = $request->get('search')) {
            $query->where('po_no', 'like', "%{$search}%");
        }

        return view('purchases.orders.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('purchases.orders.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'vendors' => Vendor::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'poNo' => 'PO-'.now()->format('Ymd').'-'.str_pad((string) (PurchaseOrder::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'po_no' => 'required|string|unique:purchase_orders,po_no',
            'branch_id' => 'required|exists:branches,id',
            'vendor_id' => 'required|exists:vendors,id',
            'po_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $data['created_by'] = auth()->id();
        $this->purchaseService->createPurchaseOrder($data, $data['items']);

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['items.part', 'vendor', 'branch']);

        return view('purchases.orders.show', [
            'purchaseOrder' => $purchaseOrder,
            'locations' => Location::with('branch')->get(),
        ]);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validate([
            'invoice_no' => 'required|string|unique:purchase_invoices,invoice_no',
            'invoice_date' => 'required|date',
            'vendor_invoice_no' => 'nullable|string',
            'default_location_id' => 'required|exists:locations,id',
            'status' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $data['created_by'] = auth()->id();
        $postStock = $data['status'] === 'posted';

        try {
            $invoice = $this->purchaseService->createInvoiceFromPurchaseOrder(
                $purchaseOrder,
                $data,
                $data['items'],
                $postStock
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-invoices.index')
            ->with('success', 'Purchase invoice '.$invoice->invoice_no.' created from PO.');
    }
}
