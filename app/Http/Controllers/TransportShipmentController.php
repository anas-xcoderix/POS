<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\SalesInvoice;
use App\Models\TransportDriver;
use App\Models\TransportShipment;
use App\Services\BranchScopeService;
use App\Services\TransportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportShipmentController extends Controller
{
    public function __construct(
        private TransportService $transportService,
        private BranchScopeService $branchScope,
    ) {}

    public function index(Request $request): View
    {
        $query = TransportShipment::with(['customer', 'branch', 'driver', 'deliveryNote', 'salesInvoice'])
            ->latest('ship_date');

        $this->branchScope->apply($query);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('shipment_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($driverId = $request->get('driver_id')) {
            $query->where('transport_driver_id', $driverId);
        }

        return view('transport.shipments.index', [
            'records' => $query->paginate(15)->withQueryString(),
            'search' => $search,
            'status' => $status,
            'driverId' => $driverId,
            'drivers' => TransportDriver::where('is_active', true)->orderBy('name')->get(),
            'statuses' => TransportShipment::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $deliveryNote = $request->filled('delivery_note_id')
            ? DeliveryNote::with(['customer', 'salesInvoice'])->find($request->delivery_note_id)
            : null;

        $invoice = $request->filled('sales_invoice_id')
            ? SalesInvoice::with('customer')->find($request->sales_invoice_id)
            : ($deliveryNote?->salesInvoice);

        return view('transport.shipments.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'drivers' => TransportDriver::where('is_active', true)->orderBy('name')->get(),
            'deliveryNotes' => DeliveryNote::with('customer')->latest()->take(50)->get(),
            'salesInvoices' => SalesInvoice::where('status', 'posted')->whereNull('voided_at')->latest()->take(50)->get(),
            'deliveryNote' => $deliveryNote,
            'invoice' => $invoice,
            'shipmentNo' => $this->transportService->nextShipmentNo(),
            'defaultBranchId' => $this->branchScope->defaultBranchId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipment_no' => 'required|string|unique:transport_shipments,shipment_no',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'transport_driver_id' => 'nullable|exists:transport_drivers,id',
            'delivery_note_id' => 'nullable|exists:delivery_notes,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'ship_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'status' => 'required|string|in:'.implode(',', TransportShipment::STATUSES),
            'ship_to_address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:30',
            'transport_charge' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
            'vehicle_plate' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $data['transport_charge'] = $data['transport_charge'] ?? 0;
        $data['cod_amount'] = $data['cod_amount'] ?? 0;

        if ($data['status'] === 'dispatched') {
            $data['dispatched_at'] = now();
        }

        if ($data['status'] === 'delivered') {
            $data['delivered_at'] = now();
        }

        try {
            $shipment = $this->transportService->createShipment($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('transport.shipments.show', $shipment)
            ->with('success', __('messages.transport.shipment_created'));
    }

    public function show(TransportShipment $shipment): View
    {
        $shipment->load(['customer', 'branch', 'driver', 'deliveryNote.items.part', 'salesInvoice', 'cashVoucherItems.voucher']);

        return view('transport.shipments.show', [
            'shipment' => $shipment,
            'statuses' => TransportShipment::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, TransportShipment $shipment): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:'.implode(',', TransportShipment::STATUSES),
        ]);

        try {
            $this->transportService->updateStatus($shipment, $data['status']);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('messages.transport.status_updated'));
    }

    public function createFromDeliveryNote(DeliveryNote $deliveryNote): RedirectResponse
    {
        try {
            $shipment = $this->transportService->createFromDeliveryNote($deliveryNote);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('transport.shipments.show', $shipment)
            ->with('success', __('messages.transport.shipment_created'));
    }
}
