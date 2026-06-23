<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Part;
use App\Models\SalesInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryNoteController extends Controller
{
    public function index(Request $request): View
    {
        $query = DeliveryNote::with(['customer', 'branch'])->latest();

        return view('delivery-notes.index', [
            'records' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $invoice = $request->filled('sales_invoice_id')
            ? SalesInvoice::with('items.part')->find($request->sales_invoice_id)
            : null;

        return view('delivery-notes.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'customers' => Customer::where('is_active', true)->get(),
            'salesInvoices' => SalesInvoice::where('status', 'posted')->whereNull('voided_at')->latest()->take(50)->get(),
            'invoice' => $invoice,
            'parts' => Part::where('is_active', true)->orderBy('part_number')->get(),
            'dnNo' => 'DN-'.now()->format('Ymd').'-'.str_pad((string) (DeliveryNote::count() + 1), 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dn_no' => 'required|string|unique:delivery_notes,dn_no',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'required|date',
            'driver_name' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|exists:parts,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($data) {
            $note = DeliveryNote::create(array_merge($data, [
                'status' => 'delivered',
                'created_by' => auth()->id(),
            ]));

            foreach ($data['items'] as $row) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $note->id,
                    'part_id' => $row['part_id'],
                    'quantity' => $row['quantity'],
                ]);
            }
        });

        return redirect()->route('delivery-notes.index')->with('success', 'Delivery note created.');
    }

    public function show(DeliveryNote $deliveryNote): View
    {
        $deliveryNote->load(['items.part', 'customer', 'branch', 'salesInvoice']);

        return view('delivery-notes.show', ['note' => $deliveryNote]);
    }
}
