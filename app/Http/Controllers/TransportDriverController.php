<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\TransportDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportDriverController extends MasterDataController
{
    protected function modelClass(): string
    {
        return TransportDriver::class;
    }

    protected function viewPath(): string
    {
        return 'transport-drivers';
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
            'code' => 'required|string|max:20|unique:transport_drivers,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'license_no' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ];
    }

    public function index(Request $request): View
    {
        $query = TransportDriver::query()->with('branch');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        return view('transport-drivers.index', [
            'records' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'search' => $search,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
