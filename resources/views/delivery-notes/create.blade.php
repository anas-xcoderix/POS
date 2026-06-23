@php $title = __('modules.new_delivery_note'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('delivery-notes.store') }}" class="space-y-6" id="dnForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-sm text-orange-700">1</span>
            Prefill from Invoice
        </h3>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field label="Sales Invoice" name="prefill_invoice" type="select" class="flex-1" hint="Optional — loads customer and line items">
                <option value="">— None —</option>
                @foreach($salesInvoices as $inv)
                    <option value="{{ $inv->id }}" @selected($invoice?->id == $inv->id)>{{ $inv->invoice_no }} — {{ $inv->customer?->name }}</option>
                @endforeach
            </x-ui.form-field>
            <button type="button" onclick="prefillFromInvoice()" class="erp-btn-secondary shrink-0">Load Invoice</button>
        </div>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-sm text-orange-700">2</span>
            Delivery Details
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="DN No" name="dn_no" :value="old('dn_no', $dnNo)" required />
            <x-ui.form-field label="Delivery Date" name="delivery_date" type="date" :value="old('delivery_date', date('Y-m-d'))" required />
            <x-ui.form-field label="Sales Invoice" name="sales_invoice_id" type="select" id="salesInvoiceId">
                <option value="">— None —</option>
                @foreach($salesInvoices as $inv)
                    <option value="{{ $inv->id }}" @selected(old('sales_invoice_id', $invoice?->id) == $inv->id)>{{ $inv->invoice_no }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required id="branchSelect">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id', $invoice?->branch_id) == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required id="customerSelect">
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id', $invoice?->customer_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Driver Name" name="driver_name" :value="old('driver_name')" />
            <x-ui.form-field label="Vehicle Plate" name="vehicle_plate" :value="old('vehicle_plate')" />
            <x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" class="md:col-span-2 lg:col-span-3" :value="old('remarks')" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-sm text-orange-700">3</span>
                Items
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr><th>Part</th><th>Qty</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('delivery-notes.index') }}" class="erp-btn-secondary text-center">{{ __('ui.cancel') }}</a>
        <button type="submit" class="erp-btn-primary">Save Delivery Note</button>
    </div>
</form>

<template id="rowTemplate">
    <tr>
        <td>
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">
                @foreach($parts as $p)
                    <option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>

<script>
let rowIndex = 0;

function addRow(partId = null, qty = 1) {
    const html = document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    const row = document.getElementById('itemsBody').lastElementChild;
    if (partId) {
        row.querySelector('select').value = partId;
    }
    if (qty) {
        row.querySelector('input[type="number"]').value = qty;
    }
}

function prefillFromInvoice() {
    const id = document.querySelector('[name="prefill_invoice"]').value;
    if (id) {
        window.location.href = '{{ route('delivery-notes.create') }}?sales_invoice_id=' + id;
    }
}

@if($invoice)
    @foreach($invoice->items as $item)
        addRow({{ $item->part_id }}, {{ $item->quantity }});
    @endforeach
@else
    addRow();
@endif
</script>
</x-erp-layout>
