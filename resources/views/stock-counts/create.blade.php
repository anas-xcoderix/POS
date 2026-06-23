@php $title = __('modules.new_stock_count'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('stock-counts.store') }}" class="space-y-6" id="countForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-sm text-violet-700">1</span>
            Count Session
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Count No" name="count_no" :value="$countNo" required />
            <x-ui.form-field label="{{ __('ui.date') }}" name="count_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Default Location" name="location_id" type="select" hint="Optional — pre-selects location on lines">
                <option value="">— None —</option>
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" class="md:col-span-2" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-sm text-violet-700">2</span>
                Count Lines
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Part</th><th>Location</th><th>Counted Qty</th><th></th>
                </tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('stock-counts.index') }}" class="erp-btn-secondary text-center">{{ __('ui.cancel') }}</a>
        <button type="submit" class="erp-btn-primary">Save Count (Draft)</button>
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
            <select name="items[__INDEX__][location_id]" required class="erp-input !mt-0 location-select">
                @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][counted_qty]" value="0" required class="erp-input !mt-0 w-28"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let rowIndex = 0;
const defaultLocation = document.querySelector('[name="location_id"]');
function addRow() {
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', rowIndex++));
    if (defaultLocation && defaultLocation.value) {
        const row = document.getElementById('itemsBody').lastElementChild;
        row.querySelector('.location-select').value = defaultLocation.value;
    }
}
defaultLocation?.addEventListener('change', () => {
    document.querySelectorAll('.location-select').forEach(sel => {
        if (!sel.value || sel.value === defaultLocation.dataset.prev) sel.value = defaultLocation.value;
    });
    defaultLocation.dataset.prev = defaultLocation.value;
});
addRow();
</script>
</x-erp-layout>
