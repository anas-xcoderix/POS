<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Franchise;
use App\Models\Location;
use App\Models\Origin;
use App\Models\Part;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartController extends Controller
{
    public function index(Request $request): View
    {
        $query = Part::query()->with(['brand', 'origin', 'franchise', 'defaultLocation']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                    ->orWhere('oem_no', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        return view('parts.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'brands' => Brand::orderBy('name')->get(),
            'origins' => Origin::orderBy('name')->get(),
            'franchises' => Franchise::orderBy('name')->get(),
            'locations' => Location::with('branch')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Part::create($request->validate($this->rules()));

        return back()->with('success', __('messages.part.created'));
    }

    public function update(Request $request, Part $part): RedirectResponse
    {
        $part->update($request->validate($this->rules($part)));

        return back()->with('success', __('messages.part.updated'));
    }

    public function destroy(Part $part): RedirectResponse
    {
        $part->delete();

        return back()->with('success', __('messages.part.deleted'));
    }

    protected function rules(?Part $part = null): array
    {
        $id = $part?->id;

        return [
            'part_number' => 'required|string|max:100|unique:parts,part_number,'.$id,
            'oem_no' => 'nullable|string|max:100',
            'manufacturer_part_no' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100|unique:parts,barcode,'.$id,
            'brand_id' => 'required|exists:brands,id',
            'origin_id' => 'nullable|exists:origins,id',
            'franchise_id' => 'nullable|exists:franchises,id',
            'default_location_id' => 'nullable|exists:locations,id',
            'description_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'list_price' => 'nullable|numeric|min:0',
            'price_2' => 'nullable|numeric|min:0',
            'price_3' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
