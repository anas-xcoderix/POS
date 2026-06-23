@php $title = 'New Sale Return'; @endphp
<x-erp-layout>
<form method="POST" action="{{ route('sale-returns.store') }}" class="space-y-6" id="returnForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-sm text-amber-700">1</span>
            Return Details
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Return No" name="return_no" :value="$returnNo" required />
            <x-ui.form-field label="Date" name="return_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required id="customerSelect">
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected($salesInvoice && $salesInvoice->customer_id == $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Original Invoice" name="sales_invoice_id" type="select" hint="Optional — pre-fills lines">
                <option value="">— None —</option>
                @foreach($salesInvoices as $inv)
                    <option value="{{ $inv->id }}" @selected($salesInvoice && $salesInvoice->id == $inv->id)>{{ $inv->invoice_no }} — {{ $inv->customer?->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select" hint="Posted restores stock immediately">
                <option value="draft">Draft</option>
                <option value="posted">Posted — restock now</option>
            </x-ui.form-field>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-sm text-amber-700">2</span>
                Return Items
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Part</th><th>Location</th><th>Qty</th><th>Unit Price</th><th></th>
                </tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('sale-returns.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary">Save Return</button>
    </div>
</form>

<template id="rowTemplate">
    <tr class="bg-white">
        <td class="min-w-[220px]">
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">
                @foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>@endforeach
            </select>
        </td>
        <td class="min-w-[160px]">
            <select name="items[__INDEX__][location_id]" required class="erp-input !mt-0">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
function addRow(partId = null, qty = 1, price = 0) {
    const html = document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    const row = document.getElementById('itemsBody').lastElementChild;
    if (partId) row.querySelector('[name$="[part_id]"]').value = partId;
    if (qty) row.querySelector('[name$="[quantity]"]').value = qty;
    if (price) row.querySelector('[name$="[unit_price]"]').value = price;
}
@if($salesInvoice)
    @foreach($salesInvoice->items as $item)
        addRow({{ $item->part_id }}, {{ $item->quantity }}, {{ $item->unit_price }});
    @endforeach
@else
    addRow();
@endif
</script>
</x-erp-layout>
