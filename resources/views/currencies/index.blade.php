@php $title = __('nav.currencies'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.search_placeholder') }}" class="erp-input !mt-0">
        </form>
        <button type="button" onclick="document.getElementById('createModal').showModal()" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Currency
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Code</th><th>Name</th><th>Symbol</th><th>Rate</th><th>Base</th><th>Active</th><th class="text-right">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->symbol }}</td>
                        <td>{{ number_format($row->exchange_rate, 4) }}</td>
                        <td>{{ $row->is_base ? 'Yes' : 'No' }}</td>
                        <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-right space-x-1">
                            @if(!$row->is_base)
                                <button type="button" onclick="openRate({{ $row->id }}, {{ $row->exchange_rate }})" class="erp-btn-ghost !py-1.5 !px-3 text-xs">Set Rate</button>
                            @endif
                            <button type="button" onclick="openEdit(@json($row))" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.edit') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state title="{{ __('pages.empty.currencies') }}" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>

<dialog id="createModal" class="rounded-xl p-6 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="{{ route('currencies.store') }}" class="space-y-4">
        @csrf
        <h4 class="font-bold">New Currency</h4>
        <x-ui.form-field label="Code" name="code" required />
        <x-ui.form-field label="{{ __('ui.name') }}" name="name" required />
        <x-ui.form-field label="{{ __('ui.name_ar') }}" name="name_ar" />
        <x-ui.form-field label="Symbol" name="symbol" />
        <x-ui.form-field label="Exchange Rate" name="exchange_rate" type="number" step="0.000001" value="1" required />
        <x-ui.form-field label="{{ __('ui.active') }}" name="is_active" type="checkbox" checked>Active</x-ui.form-field>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
            <button class="erp-btn-primary">{{ __('ui.save') }}</button>
        </div>
    </form>
</dialog>

<dialog id="editModal" class="rounded-xl p-6 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" id="editForm" class="space-y-4">
        @csrf @method('PUT')
        <h4 class="font-bold">Edit Currency</h4>
        <x-ui.form-field label="Code" name="code" id="edit_code" required />
        <x-ui.form-field label="{{ __('ui.name') }}" name="name" id="edit_name" required />
        <x-ui.form-field label="{{ __('ui.name_ar') }}" name="name_ar" id="edit_name_ar" />
        <x-ui.form-field label="Symbol" name="symbol" id="edit_symbol" />
        <x-ui.form-field label="Exchange Rate" name="exchange_rate" type="number" step="0.000001" id="edit_rate" required />
        <x-ui.form-field label="{{ __('ui.active') }}" name="is_active" type="checkbox" id="edit_active">Active</x-ui.form-field>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
            <button class="erp-btn-primary">Update</button>
        </div>
    </form>
</dialog>

<dialog id="rateModal" class="rounded-xl p-6 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" id="rateForm" class="space-y-4">
        @csrf
        <h4 class="font-bold">Set Exchange Rate</h4>
        <x-ui.form-field label="Exchange Rate" name="exchange_rate" type="number" step="0.000001" id="rate_value" required />
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">{{ __('ui.cancel') }}</button>
            <button class="erp-btn-primary">Update Rate</button>
        </div>
    </form>
</dialog>
<script>
function openEdit(row) {
    document.getElementById('editForm').action = '/currencies/' + row.id;
    document.getElementById('edit_code').value = row.code;
    document.getElementById('edit_name').value = row.name;
    document.getElementById('edit_name_ar').value = row.name_ar || '';
    document.getElementById('edit_symbol').value = row.symbol || '';
    document.getElementById('edit_rate').value = row.exchange_rate;
    document.getElementById('edit_active').checked = !!row.is_active;
    document.getElementById('editModal').showModal();
}
function openRate(id, rate) {
    document.getElementById('rateForm').action = '/currencies/' + id + '/rate';
    document.getElementById('rate_value').value = rate;
    document.getElementById('rateModal').showModal();
}
</script>
</x-erp-layout>
