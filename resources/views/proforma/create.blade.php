@php $title = __('modules.new_proforma'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('proforma-invoices.store') }}" class="space-y-6">
    @csrf
    <div class="erp-card p-6">
        <h3 class="mb-4 text-base font-bold">Proforma Details</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Proforma No" name="proforma_no" :value="$proformaNo" required />
            <x-ui.form-field label="{{ __('ui.date') }}" name="proforma_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Valid Until" name="valid_until" type="date" />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('forms.currency') }}" name="currency_id" type="select">
                <option value="">{{ __('pages.filter.base_currency') }}</option>
                @foreach($currencies as $cur)<option value="{{ $cur->id }}">{{ $cur->code }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select">
                <option value="draft">Draft</option><option value="sent">Sent</option>
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" class="md:col-span-2" />
        </div>
    </div>
    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-bold">Line Items</h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">Add Line</button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr><th>Part</th><th>Qty</th><th>Unit Price</th><th>Disc %</th><th>VAT %</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="flex justify-end gap-3">
        <a href="{{ route('proforma-invoices.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary">Save Proforma</button>
    </div>
</form>
<template id="rowTemplate">
    <tr>
        <td><select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">@foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }}</option>@endforeach</select></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][unit_price]" value="0" class="erp-input !mt-0 w-28"></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][discount_percent]" value="0" class="erp-input !mt-0 w-20"></td>
        <td><input type="number" step="0.01" name="items[__INDEX__][vat_percent]" value="15" class="erp-input !mt-0 w-20"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2">×</button></td>
    </tr>
</template>
<script>let i=0;function addRow(){document.getElementById('itemsBody').insertAdjacentHTML('beforeend',document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__',i++));}addRow();</script>
</x-erp-layout>
