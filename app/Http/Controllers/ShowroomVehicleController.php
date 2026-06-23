<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Franchise;
use App\Models\ShowroomColor;
use App\Models\ShowroomVehicle;
use App\Models\ShowroomVehicleModel;
use App\Models\ShowroomVehicleTransfer;
use App\Services\BranchScopeService;
use App\Services\ShowroomVehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowroomVehicleController extends Controller
{
    public function __construct(
        private ShowroomVehicleService $service,
        private BranchScopeService $branchScope,
    ) {}

    public function index(Request $request): View
    {
        $query = ShowroomVehicle::with(['branch', 'model', 'color', 'customer'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('stock_no', 'like', "%{$search}%")
                    ->orWhere('chassis_no', 'like', "%{$search}%");
            });
        }

        return view('showroom-vehicles.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('showroom-vehicles.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVehicle($request);
        $data['created_by'] = auth()->id();
        $data['status'] = 'in_stock';

        try {
            $this->service->register($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('showroom-vehicles.index')->with('success', __('messages.showroom.vehicle_registered'));
    }

    public function show(ShowroomVehicle $showroomVehicle): View
    {
        $showroomVehicle->load(['branch', 'model', 'color', 'franchise', 'customer', 'transfers.fromBranch', 'transfers.toBranch']);

        return view('showroom-vehicles.show', array_merge($this->formData(), [
            'vehicle' => $showroomVehicle,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function transfer(Request $request, ShowroomVehicle $showroomVehicle): RedirectResponse
    {
        $data = $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $this->service->transfer($showroomVehicle, (int) $data['to_branch_id'], auth()->id(), $data['remarks'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.showroom.transfer_created'));
    }

    public function receiveTransfer(ShowroomVehicleTransfer $showroomVehicleTransfer): RedirectResponse
    {
        try {
            $this->service->receiveTransfer($showroomVehicleTransfer, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.showroom.transfer_received'));
    }

    public function sell(Request $request, ShowroomVehicle $showroomVehicle): RedirectResponse
    {
        $data = $request->validate(['customer_id' => 'required|exists:customers,id']);

        try {
            $this->service->markSold($showroomVehicle, (int) $data['customer_id']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.showroom.vehicle_sold'));
    }

    protected function validateVehicle(Request $request): array
    {
        return $request->validate([
            'stock_no' => 'required|string|unique:showroom_vehicles,stock_no',
            'branch_id' => 'required|exists:branches,id',
            'model_id' => 'required|exists:showroom_vehicle_models,id',
            'color_id' => 'nullable|exists:showroom_colors,id',
            'franchise_id' => 'nullable|exists:franchises,id',
            'chassis_no' => 'required|string|unique:showroom_vehicles,chassis_no',
            'engine_no' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1990|max:2100',
            'purchase_cost' => 'nullable|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
            'received_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
    }

    protected function formData(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'models' => ShowroomVehicleModel::where('is_active', true)->orderBy('name')->get(),
            'colors' => ShowroomColor::where('is_active', true)->orderBy('name')->get(),
            'franchises' => Franchise::where('is_active', true)->orderBy('name')->get(),
            'defaultBranchId' => $this->branchScope->defaultBranchId(),
            'stockNo' => 'SV-'.now()->format('Ymd').'-'.str_pad((string) (ShowroomVehicle::count() + 1), 4, '0', STR_PAD_LEFT),
        ];
    }
}
