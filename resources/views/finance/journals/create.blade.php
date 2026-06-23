@php $title = __('modules.manual_journal'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('journal-entries.store') }}" class="space-y-6" id="journalForm">
    @csrf

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-sm text-blue-700">1</span>
            Entry Details
        </h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field label="Entry No" name="entry_no" :value="$entryNo" hint="Auto-generated if left blank" />
            <x-ui.form-field label="Date" name="entry_date" type="date" :value="date('Y-m-d')" required />
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Description" name="description" required class="md:col-span-2 lg:col-span-3" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-sm text-blue-700">2</span>
                Journal Lines
            </h3>
            <button type="button" onclick="addRow()" class="erp-btn-ghost text-sm">
                <x-ui.icon name="plus" class="h-4 w-4" /> Add Line
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Account</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th></th>
                </tr></thead>
                <tbody id="linesBody"></tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="2" class="text-right font-semibold">Totals</td>
                        <td class="text-right font-bold" id="totalDebit">0.00</td>
                        <td class="text-right font-bold" id="totalCredit">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="border-t border-slate-100 px-6 py-3 text-xs text-slate-500">Debits must equal credits before posting. Minimum 2 lines required.</p>
    </div>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('journal-entries.index') }}" class="erp-btn-secondary text-center">Cancel</a>
        <button type="submit" class="erp-btn-primary" onclick="return validateJournal()">Post Journal</button>
    </div>
</form>

<template id="rowTemplate">
    <tr class="bg-white journal-line">
        <td class="min-w-[240px]">
            <select name="lines[__INDEX__][account_id]" required class="erp-input !mt-0">
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}">{{ $a->account_code }} — {{ $a->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="min-w-[180px]">
            <input type="text" name="lines[__INDEX__][description]" class="erp-input !mt-0" placeholder="Line memo">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="lines[__INDEX__][debit]" value="0" class="erp-input !mt-0 w-28 debit-input text-right" oninput="recalcTotals()">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="lines[__INDEX__][credit]" value="0" class="erp-input !mt-0 w-28 credit-input text-right" oninput="recalcTotals()">
        </td>
        <td><button type="button" onclick="removeRow(this)" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button></td>
    </tr>
</template>
<script>
let lineIndex = 0;
function addRow() {
    document.getElementById('linesBody').insertAdjacentHTML('beforeend', document.getElementById('rowTemplate').innerHTML.replaceAll('__INDEX__', lineIndex++));
    recalcTotals();
}
function removeRow(btn) {
    btn.closest('tr').remove();
    recalcTotals();
}
function recalcTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('.debit-input').forEach(el => debit += parseFloat(el.value) || 0);
    document.querySelectorAll('.credit-input').forEach(el => credit += parseFloat(el.value) || 0);
    document.getElementById('totalDebit').textContent = debit.toFixed(2);
    document.getElementById('totalCredit').textContent = credit.toFixed(2);
}
function validateJournal() {
    const debit = parseFloat(document.getElementById('totalDebit').textContent);
    const credit = parseFloat(document.getElementById('totalCredit').textContent);
    if (debit !== credit || debit === 0) {
        alert('Debits and credits must balance and be greater than zero.');
        return false;
    }
    return true;
}
addRow();
addRow();
</script>
</x-erp-layout>
