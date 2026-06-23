@php $title = 'Edit Posted Purchase — '.$invoice->invoice_no; @endphp
<x-erp-layout>
<form method="POST" action="{{ route('purchase-invoices.update-posted', $invoice) }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="erp-card p-6">
        <h3 class="mb-4 font-bold">Invoice {{ $invoice->invoice_no }}</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-ui.form-field label="Date" name="invoice_date" type="date" :value="$invoice->invoice_date?->format('Y-m-d')" required />
            <x-ui.form-field label="Vendor Invoice No" name="vendor_invoice_no" :value="$invoice->vendor_invoice_no" />
            <x-ui.form-field label="Remarks" name="remarks" :value="$invoice->remarks" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="font-bold">Line Items</h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">Add Line</button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr><th>Part</th><th>Location</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                <tbody id="itemsBody">
                    @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td><select name="items[{{ $idx }}][part_id]" required class="erp-input !mt-0">@foreach($parts as $p)<option value="{{ $p->id }}" @selected($item->part_id == $p->id)>{{ $p->part_number }}</option>@endforeach</select></td>
                            <td><select name="items[{{ $idx }}][location_id]" class="erp-input !mt-0">@foreach($locations as $l)<option value="{{ $l->id }}" @selected($item->location_id == $l->id)>{{ $l->code }}</option>@endforeach</select></td>
                            <td><input type="number" step="0.01" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required class="erp-input !mt-0 w-24"></td>
                            <td><input type="number" step="0.01" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}" required class="erp-input !mt-0 w-28"></td>
                            <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2">×</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('purchase-invoices.index') }}" class="erp-btn-secondary">Cancel</a>
        <button class="erp-btn-primary">Update Posted Invoice</button>
    </div>
</form>

<template id="rowTemplate">
    <tr>
        <td><select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">@foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }}</option>@endforeach</select></td>
        <td><select name="items[__INDEX__][location_id]" class="erp-input !mt-0">@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->code }}</option>@endforeach</select></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2">×</button></td>
    </tr>
</template>
<script>
let rowIndex = {{ $invoice->items->count() }};
function addRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
}
</script>
</x-erp-layout>
