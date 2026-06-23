<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleExpenseController extends Controller
{
    public function index(Vehicle $vehicle): View
    {
        return view('vehicles.expenses', [
            'vehicle' => $vehicle->load('customer'),
            'records' => VehicleExpense::where('vehicle_id', $vehicle->id)->latest('expense_date')->get(),
        ]);
    }

    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'expense_date' => 'required|date',
            'expense_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        VehicleExpense::create(array_merge($data, [
            'vehicle_id' => $vehicle->id,
            'created_by' => auth()->id(),
        ]));

        return back()->with('success', __('messages.vehicle.expense_recorded'));
    }

    public function destroy(VehicleExpense $vehicleExpense): RedirectResponse
    {
        $vehicleExpense->delete();

        return back()->with('success', __('messages.vehicle.expense_removed'));
    }
}
