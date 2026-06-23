@php $title = __('modules.new_stock_transfer'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('stock-transfers.store') }}" class="space-y-6">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-sm text-violet-700">1</span>
            Transfer Details
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Transfer No" name="transfer_no" :value="$transferNo" required />
            <x-ui.form-field label="Date" name="transfer_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="From Branch" name="from_branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="To Branch" name="to_branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Remarks" name="remarks" class="md:col-span-2" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-sm text-violet-700">2</span>
                Items to Transfer
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Part</th><th>From Location</th><th>To Location</th><th>Qty</th><th></th>
                </tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('stock-transfers.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary">Save Transfer (Draft)</button>
    </div>
</form>

<template id="rowTemplate">
    <tr class="bg-white">
        <td class="min-w-[200px]">
            <select name="items[__INDEX__][part_id]" required class="erp-input !mt-0">
                @foreach($parts as $p)<option value="{{ $p->id }}">{{ $p->part_number }}</option>@endforeach
            </select>
        </td>
        <td class="min-w-[150px]">
            <select name="items[__INDEX__][from_location_id]" required class="erp-input !mt-0">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </select>
        </td>
        <td class="min-w-[150px]">
            <select name="items[__INDEX__][to_location_id]" required class="erp-input !mt-0">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="items[__INDEX__][quantity]" value="1" required class="erp-input !mt-0 w-24"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
function addRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
}
addRow();
</script>
</x-erp-layout>
