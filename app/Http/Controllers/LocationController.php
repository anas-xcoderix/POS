<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class LocationController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Location::class;
    }

    protected function viewPath(): string
    {
        return 'locations';
    }

    protected function withRelations(): array
    {
        return ['branch'];
    }

    protected function extraViewData(): array
    {
        return ['branches' => Branch::where('is_active', true)->orderBy('name')->get()];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'branch_id' => 'required|exists:branches,id',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'aisle' => 'nullable|string|max:50',
            'rack' => 'nullable|string|max:50',
            'bin' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ];
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        $query = Location::query()->with('branch');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return view('locations.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
