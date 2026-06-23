<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\FixedAssetCategory;
use App\Models\Location;
use App\Services\FixedAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixedAssetController extends Controller
{
    public function __construct(private FixedAssetService $fixedAssetService) {}

    public function index(Request $request): View
    {
        $query = FixedAsset::with(['category', 'branch'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return view('fixed-assets.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('fixed-assets.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'categories' => FixedAssetCategory::where('is_active', true)->get(),
            'locations' => Location::with('branch')->get(),
            'assetCode' => 'FA-'.now()->format('Ymd').'-'.str_pad((string) (FixedAsset::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'category_id' => 'required|exists:fixed_asset_categories,id',
            'branch_id' => 'required|exists:branches,id',
            'location_id' => 'nullable|exists:locations,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_value' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'nullable|integer|min:1',
            'depreciation_method' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['salvage_value'] = $data['salvage_value'] ?? 0;

        try {
            $this->fixedAssetService->register($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('fixed-assets.index')->with('success', __('messages.fixed_asset.registered'));
    }

    public function show(FixedAsset $fixedAsset): View
    {
        $fixedAsset->load(['category', 'branch', 'location', 'depreciations']);

        return view('fixed-assets.show', ['asset' => $fixedAsset]);
    }

    public function runDepreciation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $count = $this->fixedAssetService->runMonthlyDepreciation(
            (int) $data['year'],
            (int) $data['month'],
            auth()->id()
        );

        return back()->with('success', __('messages.fixed_asset.depreciation_posted', ['count' => $count]));
    }
}
