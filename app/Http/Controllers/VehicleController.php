<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class VehicleController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Vehicle::class;
    }

    protected function viewPath(): string
    {
        return 'workshop.vehicles';
    }

    protected function searchableColumns(): array
    {
        return ['plate_no', 'make', 'model', 'vin'];
    }

    protected function withRelations(): array
    {
        return ['customer'];
    }

    protected function extraViewData(): array
    {
        return ['customers' => Customer::where('is_active', true)->orderBy('name')->get()];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'customer_id' => 'nullable|exists:customers,id',
            'plate_no' => 'required|string|max:20|unique:vehicles,plate_no,'.$id,
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|string|max:10',
            'vin' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'istimara_expiry' => 'nullable|date',
            'remarks' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        $query = Vehicle::query()->with('customer');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('plate_no', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%");
            });
        }

        return view('workshop.vehicles.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
