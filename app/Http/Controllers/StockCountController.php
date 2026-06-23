<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Part;
use App\Models\StockCountSession;
use App\Services\StockCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockCountController extends Controller
{
    public function __construct(private StockCountService $stockCountService) {}

    public function index(Request $request): View
    {
        $query = StockCountSession::with(['branch', 'location'])->latest();

        if ($search = $request->get('search')) {
            $query->where('count_no', 'like', "%{$search}%");
        }

        return view('stock-counts.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('stock-counts.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'locations' => Location::with('branch')->get(),
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(),
            'countNo' => 'CNT-'.now()->format('Ymd').'-'.str_pad((string) (StockCountSession::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'count_no' => 'required|string|unique:stock_count_sessions,count_no',
            'branch_id' => 'required|exists:branches,id',
            'location_id' => 'nullable|exists:locations,id',
            'count_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.location_id' => 'required|exists:locations,id',
            'items.*.counted_qty' => 'required|numeric|min:0',
        ]);

        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        try {
            $this->stockCountService->createSession($data, $data['items']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-counts.index')->with('success', 'Stock count session saved.');
    }

    public function show(StockCountSession $stockCount): View
    {
        $stockCount->load(['items.part', 'items.location', 'branch']);

        return view('stock-counts.show', ['session' => $stockCount]);
    }

    public function post(StockCountSession $stockCount): RedirectResponse
    {
        try {
            $this->stockCountService->postSession($stockCount, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Count posted — variances adjusted.');
    }
}
