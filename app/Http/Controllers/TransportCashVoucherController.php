<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\TransportCashVoucher;
use App\Models\TransportDriver;
use App\Models\TransportShipment;
use App\Services\BranchScopeService;
use App\Services\TransportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportCashVoucherController extends Controller
{
    public function __construct(
        private TransportService $transportService,
        private BranchScopeService $branchScope,
    ) {}

    public function index(Request $request): View
    {
        $query = TransportCashVoucher::with(['driver', 'branch'])->latest('voucher_date');

        $this->branchScope->apply($query);

        if ($search = $request->get('search')) {
            $query->where('voucher_no', 'like', "%{$search}%");
        }

        return view('transport.cash-vouchers.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $driverId = $request->get('driver_id');

        $shipments = TransportShipment::with(['customer'])
            ->where('cod_amount', '>', 0)
            ->whereColumn('cod_collected', '<', 'cod_amount')
            ->whereIn('status', ['dispatched', 'in_transit', 'delivered'])
            ->when($driverId, fn ($q) => $q->where('transport_driver_id', $driverId))
            ->orderBy('ship_date')
            ->get();

        return view('transport.cash-vouchers.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'drivers' => TransportDriver::where('is_active', true)->orderBy('name')->get(),
            'shipments' => $shipments,
            'driverId' => $driverId,
            'voucherNo' => $this->transportService->nextVoucherNo(),
            'defaultBranchId' => $this->branchScope->defaultBranchId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_no' => 'required|string|unique:transport_cash_vouchers,voucher_no',
            'branch_id' => 'required|exists:branches,id',
            'transport_driver_id' => 'required|exists:transport_drivers,id',
            'voucher_date' => 'required|date',
            'remarks' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.shipment_id' => 'required|exists:transport_shipments,id',
            'lines.*.amount' => 'required|numeric|min:0.01',
        ]);

        $data['created_by'] = auth()->id();

        $lines = collect($data['lines'])->map(fn ($row) => [
            'shipment_id' => $row['shipment_id'],
            'amount' => $row['amount'],
        ])->all();

        try {
            $voucher = $this->transportService->createCashVoucher($data, $lines);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('transport.cash-vouchers.show', $voucher)
            ->with('success', __('messages.transport.voucher_created'));
    }

    public function show(TransportCashVoucher $cashVoucher): View
    {
        $cashVoucher->load(['items.shipment.customer', 'driver', 'branch', 'cashBookEntry']);

        return view('transport.cash-vouchers.show', ['voucher' => $cashVoucher]);
    }

    public function post(TransportCashVoucher $cashVoucher): RedirectResponse
    {
        try {
            $this->transportService->postCashVoucher($cashVoucher, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.transport.voucher_posted'));
    }
}
