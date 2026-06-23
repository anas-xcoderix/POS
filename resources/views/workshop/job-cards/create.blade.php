@php $title = __('modules.new_job_card'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('job-cards.store') }}" class="space-y-6">
    @csrf
    <div class="erp-card p-6">
        <h3 class="mb-4 text-base font-bold text-slate-900">Job Card Details</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Job No" name="job_no" :value="$jobNo" required />
            <x-ui.form-field label="Job Date" name="job_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Promised Date" name="promised_date" type="date" />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required id="customerSelect">
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Vehicle" name="vehicle_id" type="select">
                <option value="">— None —</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-customer="{{ $v->customer_id }}">{{ $v->plate_no }} — {{ $v->make }} {{ $v->model }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Mechanic" name="mechanic_id" type="select">
                <option value="">— Unassigned —</option>
                @foreach($mechanics as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Stock Location" name="location_id" type="select">
                <option value="">— For parts issue on invoice —</option>
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Status" name="status" type="select">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
            </x-ui.form-field>
            <x-ui.form-field label="Customer Complaint" name="complaint" class="md:col-span-2 lg:col-span-3" type="textarea" />
            <x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" class="md:col-span-2 lg:col-span-3" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Parts & Labor</h3>
            <div class="flex gap-2">
                <button type="button" onclick="addPartRow()" class="erp-btn-ghost text-sm">+ Part</button>
                <button type="button" onclick="addLaborRow()" class="erp-btn-ghost text-sm">+ Labor</button>
            </div>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr><th>Type</th><th>Part / Description</th><th>Qty</th><th>Unit Price</th><th></th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('job-cards.index') }}" class="erp-btn-secondary text-center">{{ __('ui.cancel') }}</a>
        <button type="submit" class="erp-btn-primary">Save Job Card</button>
    </div>
</form>

<template id="partRowTemplate">
    <tr>
        <td><input type="hidden" name="items[__INDEX__][item_type]" value="part">Part</td>
        <td class="min-w-[220px]">
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0" onchange="fillPartPrice(this)">
                @foreach($parts as $p)<option value="{{ $p->id }}" data-price="{{ $p->list_price }}">{{ $p->part_number }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28 price-input"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<template id="laborRowTemplate">
    <tr>
        <td><input type="hidden" name="items[__INDEX__][item_type]" value="labor">Labor</td>
        <td><input type="text" name="items[__INDEX__][description]" placeholder="{{ __('pages.job_cards.service_description') }}" required class="erp-input !mt-0"></td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][unit_price]" value="0" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
function addPartRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('partRowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
}
function addLaborRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('laborRowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
}
function fillPartPrice(sel) {
    const price = sel.selectedOptions[0]?.dataset.price || 0;
    sel.closest('tr').querySelector('.price-input').value = price;
}
addPartRow();
</script>
</x-erp-layout>
