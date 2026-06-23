<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request): View
    {
        $query = StockBalance::query()
            ->with(['part.brand', 'branch', 'location'])
            ->where('quantity', '>', 0);

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('part', function ($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%");
            });
        }

        return view('stock.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'branches' => Branch::where('is_active', true)->get(),
            'search' => $search,
            'branchId' => $branchId,
        ]);
    }

    public function movements(Request $request): View
    {
        $query = StockMovement::with(['part', 'branch', 'location', 'user'])->latest('movement_date');

        if ($type = $request->get('movement_type')) {
            $query->where('movement_type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('part', fn ($p) => $p->where('part_number', 'like', "%{$search}%"));
            });
        }

        return view('stock.movements', [
            'records' => $query->paginate(25)->withQueryString(),
            'search' => $search,
            'movementType' => $type,
        ]);
    }

    public function adjustmentForm(): View
    {
        return view('stock.adjustment', [
            'branches' => Branch::where('is_active', true)->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(),
            'locations' => Location::with('branch')->get(),
        ]);
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'location_id' => 'required|exists:locations,id',
            'part_id' => 'required|exists:parts,id',
            'new_quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $this->stockService->manualAdjustment(
                (int) $data['branch_id'],
                (int) $data['location_id'],
                (int) $data['part_id'],
                (float) $data['new_quantity'],
                auth()->id(),
                $data['remarks'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock.movements')->with('success', 'Stock adjusted successfully.');
    }
}
