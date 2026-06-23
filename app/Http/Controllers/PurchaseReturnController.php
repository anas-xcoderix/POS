<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Vendor;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function index(Request $request): View
    {
        $query = PurchaseReturn::with(['vendor', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('return_no', 'like', "%{$search}%");
        }

        return view('purchase-returns.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $purchaseInvoice = $request->filled('purchase_invoice_id')
            ? PurchaseInvoice::with('items.part')->find($request->purchase_invoice_id)
            : null;

        return view('purchase-returns.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'vendors' => Vendor::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'purchaseInvoices' => PurchaseInvoice::where('status', 'posted')->with('vendor')->latest()->take(50)->get(),
            'purchaseInvoice' => $purchaseInvoice,
            'returnNo' => 'PR-'.now()->format('Ymd').'-'.str_pad((string) (PurchaseReturn::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'return_no' => 'required|string|unique:purchase_returns,return_no',
            'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id',
            'branch_id' => 'required|exists:branches,id',
            'vendor_id' => 'required|exists:vendors,id',
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
            $this->purchaseService->createPurchaseReturn($data, $data['items'], $postStock);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase return created.');
    }

    public function post(PurchaseReturn $purchaseReturn): RedirectResponse
    {
        try {
            $this->purchaseService->postPurchaseReturn($purchaseReturn, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return posted and stock removed.');
    }
}
