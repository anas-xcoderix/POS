@php $title = 'New Quotation'; @endphp
<x-erp-layout>
<form method="POST" action="{{ route('quotations.store') }}" class="space-y-6">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-100 text-sm text-cyan-700">1</span>
            Quotation Details
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Quotation No" name="quotation_no" :value="$quotationNo" required />
            <x-ui.form-field label="Date" name="quotation_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Valid Until" name="valid_until" type="date" />
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select">
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="approved">Approved</option>
            </x-ui.form-field>
            <x-ui.form-field label="Remarks" name="remarks" class="md:col-span-2 lg:col-span-3" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-100 text-sm text-cyan-700">2</span>
                Line Items
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Part</th><th>Qty</th><th>Unit Price</th><th>Disc %</th><th>VAT %</th><th></th>
                </tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('quotations.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary">Save Quotation</button>
    </div>
</form>

<template id="rowTemplate">
    <tr class="bg-white">
        <td class="min-w-[220px]">
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0" onchange="fillPrice(this)">
                @foreach($parts as $p)<option value="{{ $p->id }}" data-price="{{ $p->list_price }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28 price-input"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount_percent]" value="0" class="erp-input !mt-0 w-20"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][vat_percent]" value="0" class="erp-input !mt-0 w-20"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
function addRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
}
function fillPrice(sel) {
    const price = sel.selectedOptions[0]?.dataset.price || 0;
    sel.closest('tr').querySelector('.price-input').value = price;
}
addRow();
</script>
</x-erp-layout>
