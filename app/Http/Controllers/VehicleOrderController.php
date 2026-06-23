<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\VehicleOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleOrderController extends Controller
{
    public function index(): View
    {
        return view('vehicle-orders.index', [
            'records' => VehicleOrder::with(['customer', 'branch'])->latest()->paginate(15),
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'orderNo' => 'VO-'.now()->format('Ymd').'-'.str_pad((string) (VehicleOrder::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_no' => 'required|string|unique:vehicle_orders,order_no',
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'vehicle_make' => 'nullable|string',
            'vehicle_model' => 'nullable|string',
            'estimated_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        VehicleOrder::create($data);

        return back()->with('success', 'Vehicle order created.');
    }

    public function update(Request $request, VehicleOrder $vehicleOrder): RedirectResponse
    {
        $vehicleOrder->update($request->validate([
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'estimated_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]));

        return back()->with('success', 'Order updated.');
    }

    public function destroy(VehicleOrder $vehicleOrder): RedirectResponse
    {
        $vehicleOrder->delete();

        return back()->with('success', 'Order deleted.');
    }
}
