@php $title = __('modules.new_sales_invoice'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('sales-invoices.store') }}" class="space-y-6" id="invoiceForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-sm text-orange-700">1</span>
            Invoice Details
        </h3>
        <div id="customerCreditInfo" class="mb-4 hidden rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900"></div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Invoice No" name="invoice_no" :value="$invoiceNo" required />
            <x-ui.form-field label="{{ __('ui.date') }}" name="invoice_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required id="branchSelect">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(isset($defaultBranchId) && $defaultBranchId == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required id="customerSelect">
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_type }})</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Payment Type" name="invoice_type" type="select" id="invoiceTypeSelect">
                <option value="cash">Cash</option>
                <option value="credit">Credit</option>
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select" hint="Posted status will deduct stock immediately">
                <option value="draft">Draft — save without posting</option>
                <option value="posted">Posted — deduct stock now</option>
            </x-ui.form-field>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-sm text-orange-700">2</span>
                Line Items
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Part</th><th>Location</th><th>Qty</th><th>Unit Price</th><th>Disc %</th><th>VAT %</th><th></th>
                </tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('sales-invoices.index') }}" class="erp-btn-secondary text-center">{{ __('ui.cancel') }}</a>
        <button type="submit" class="erp-btn-primary">Save Invoice</button>
    </div>
</form>

<template id="rowTemplate">
    <tr class="bg-white">
        <td class="min-w-[220px]">
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0 part-select" onchange="resolveLinePrice(this)">
                @foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 28) }}</option>@endforeach
            </select>
        </td>
        <td class="min-w-[160px]">
            <select name="items[__INDEX__][location_id]" class="erp-input !mt-0">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28 price-input"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][discount_percent]" value="0" class="erp-input !mt-0 w-20 disc-input"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][vat_percent]" value="0" class="erp-input !mt-0 w-20 vat-input"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
function addRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
    const row = document.getElementById('itemsBody').lastElementChild;
    resolveLinePrice(row.querySelector('.part-select'));
}
async function resolveLinePrice(selectEl) {
    const row = selectEl.closest('tr');
    const partId = selectEl.value;
    const customerId = document.getElementById('customerSelect').value;
    if (!partId) return;
    const res = await fetch(`{{ route('pricing.resolve') }}?part_id=${partId}&customer_id=${customerId}`, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) return;
    const data = await res.json();
    row.querySelector('.price-input').value = data.unit_price;
    row.querySelector('.disc-input').value = data.discount_percent;
    row.querySelector('.vat-input').value = data.vat_percent;
    updateCustomerCredit(data);
}
async function refreshCustomerCredit() {
    const customerId = document.getElementById('customerSelect').value;
    const partSelect = document.querySelector('.part-select');
    if (partSelect) resolveLinePrice(partSelect);
}
function updateCustomerCredit(data) {
    const box = document.getElementById('customerCreditInfo');
    if (data.customer_balance === null && data.available_credit === null) {
        box.classList.add('hidden');
        return;
    }
    box.classList.remove('hidden');
    box.innerHTML = `Balance: <strong>${Number(data.customer_balance || 0).toFixed(2)}</strong> · Credit limit: <strong>${Number(data.customer_credit_limit || 0).toFixed(2)}</strong> · Available: <strong>${data.available_credit === null ? 'Unlimited' : Number(data.available_credit).toFixed(2)}</strong>`;
}
document.getElementById('customerSelect').addEventListener('change', refreshCustomerCredit);
addRow();
refreshCustomerCredit();
</script>
</x-erp-layout>
