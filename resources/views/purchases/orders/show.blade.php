@php
$title = 'PO '.$purchaseOrder->po_no;
$invoiceNo = 'PI-'.now()->format('Ymd').'-'.str_pad((string) (\App\Models\PurchaseInvoice::count() + 1), 4, '0', STR_PAD_LEFT);
@endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $purchaseOrder->po_no }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $purchaseOrder->vendor?->name }} · {{ $purchaseOrder->branch?->name }}</p>
                <p class="text-sm text-slate-500">PO Date: {{ \Carbon\Carbon::parse($purchaseOrder->po_date)->format('M d, Y') }}</p>
            </div>
            <span class="erp-badge erp-badge-amber">{{ ucfirst($purchaseOrder->status) }}</span>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Part</th><th>Ordered</th><th>Received</th><th>Pending</th><th>Unit Price</th>
                </tr></thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                        @php $pending = max(0, (float)$item->quantity - (float)$item->received_qty); @endphp
                        <tr>
                            <td>
                                <span class="font-medium">{{ $item->part?->part_number }}</span>
                                <div class="text-xs text-slate-500">{{ Str::limit($item->part?->description_en, 40) }}</div>
                            </td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->received_qty, 2) }}</td>
                            <td class="font-semibold {{ $pending > 0 ? 'text-amber-700' : 'text-slate-400' }}">{{ number_format($pending, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($purchaseOrder->pendingQty() > 0)
        <div class="erp-card p-6">
            <h3 class="mb-4 text-base font-bold text-slate-900">Receive Against PO</h3>
            <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <x-ui.form-field label="Purchase Invoice No" name="invoice_no" :value="$invoiceNo" required />
                    <x-ui.form-field label="Invoice Date" name="invoice_date" type="date" :value="date('Y-m-d')" required />
                    <x-ui.form-field label="Vendor Invoice No" name="vendor_invoice_no" />
                    <x-ui.form-field label="Default Location" name="default_location_id" type="select" required>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>
                        @endforeach
                    </x-ui.form-field>
                    <x-ui.form-field label="Status" name="status" type="select" hint="Posted receives stock immediately">
                        <option value="draft">Draft</option>
                        <option value="posted">Posted — receive stock</option>
                    </x-ui.form-field>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="erp-table min-w-full">
                        <thead class="bg-slate-50/80"><tr>
                            <th>Part</th><th>Pending</th><th>Receive Qty</th>
                        </tr></thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                                @php $pending = max(0, (float)$item->quantity - (float)$item->received_qty); @endphp
                                @if($pending > 0)
                                    <tr>
                                        <td>{{ $item->part?->part_number }}</td>
                                        <td>{{ number_format($pending, 2) }}</td>
                                        <td>
                                            <input type="hidden" name="items[{{ $loop->index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                            <input type="number" step="0.01" min="0" max="{{ $pending }}" name="items[{{ $loop->index }}][quantity]" value="{{ $pending }}" class="erp-input !mt-0 w-28">
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="erp-btn-primary">Create Purchase Invoice & Receive</button>
            </form>
        </div>
    @else
        <x-ui.alert type="success">This purchase order is fully received.</x-ui.alert>
    @endif

    <a href="{{ route('purchase-orders.index') }}" class="erp-btn-secondary">Back to Purchase Orders</a>
</div>
</x-erp-layout>
