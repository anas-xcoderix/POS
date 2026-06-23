<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\StockTransfer;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(
        private PurchaseService $purchaseService,
        private StockService $stockService
    ) {}

    public function index(Request $request): View
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch'])->latest();

        if ($search = $request->get('search')) {
            $query->where('transfer_no', 'like', "%{$search}%");
        }

        return view('stock-transfers.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('stock-transfers.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->with('brand')->get(),
            'locations' => Location::with('branch')->get(),
            'transferNo' => 'TR-'.now()->format('Ymd').'-'.str_pad((string) (StockTransfer::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transfer_no' => 'required|string|unique:stock_transfers,transfer_no',
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'transfer_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.from_location_id' => 'required|exists:locations,id',
            'items.*.to_location_id' => 'required|exists:locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        $this->purchaseService->createStockTransfer($data, $data['items']);

        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer created.');
    }

    public function complete(StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            $this->stockService->completeTransfer($stockTransfer, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Transfer completed. Stock moved between branches.');
    }
}
