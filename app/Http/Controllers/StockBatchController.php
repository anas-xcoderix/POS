<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\StockBatch;
use App\Services\BranchScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockBatchController extends Controller
{
    public function __construct(private BranchScopeService $branchScope) {}

    public function index(Request $request): View
    {
        $query = StockBatch::with(['part', 'branch', 'location'])
            ->where('quantity', '>', 0)
            ->orderByDesc('received_date');

        $this->branchScope->apply($query);

        if ($partId = $request->get('part_id')) {
            $query->where('part_id', $partId);
        }

        if ($locationId = $request->get('location_id')) {
            $query->where('location_id', $locationId);
        }

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        return view('stock-batches.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(['id', 'part_number', 'description_en']),
            'locations' => Location::with('branch')->get(),
            'branches' => Branch::where('is_active', true)->get(),
            'partId' => $partId,
            'locationId' => $locationId,
            'branchId' => $branchId,
        ]);
    }
}
