@php $title = __('modules.new_purchase_order'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
    @csrf
    <div class="erp-card p-6">
        <h3 class="mb-4 text-base font-bold text-slate-900">Order Details</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="PO No" name="po_no" :value="$poNo" required />
            <x-ui.form-field label="{{ __('ui.date') }}" name="po_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Expected Date" name="expected_date" type="date" />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Vendor" name="vendor_id" type="select" required>
                @foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select">
                <option value="draft">Draft</option><option value="approved">Approved</option>
            </x-ui.form-field>
        </div>
    </div>
    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b px-6 py-4">
            <h3 class="font-bold text-slate-900">Items</h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm"><x-ui.icon name="plus" class="h-4 w-4" /> Add Line</button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr><th>Part</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('purchase-orders.index') }}" class="erp-btn-secondary text-center">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary">Save Purchase Order</button>
    </div>
</form>
<template id="rowTemplate">
    <tr>
        <td><select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">@foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }}</option>@endforeach</select></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>let rowIndex=0;function addRow(){document.getElementById('itemsBody').insertAdjacentHTML('beforeend',document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__',rowIndex++));}addRow();</script>
</x-erp-layout>
