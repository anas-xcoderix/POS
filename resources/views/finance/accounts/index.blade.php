@php $title = __('modules.chart_of_accounts'); @endphp
<x-erp-layout>
<div x-data="{ createOpen: false, editOpen: false }">
    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <form method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-end">
                <div class="relative flex-1 sm:max-w-sm">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" name="search" value="{{ $search }}" placeholder="Code or name..." class="erp-input !mt-0 pl-10">
                </div>
                <x-ui.form-field label="Type" name="account_type" type="select" class="sm:w-44">
                    <option value="">All types</option>
                    @foreach($accountTypes as $type)
                        <option value="{{ $type }}" @selected($accountType === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </x-ui.form-field>
                <div class="flex gap-2">
                    <button class="erp-btn-secondary">Filter</button>
                    <button type="button" @click="createOpen = true" class="erp-btn-primary">
                        <x-ui.icon name="plus" class="h-4 w-4" /> Add Account
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Code</th><th>Name</th><th>Type</th><th>Parent</th><th class="text-right">Balance</th><th>Status</th><th class="text-right">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $record->account_code }}</td>
                            <td>{{ $record->name }}</td>
                            <td><span class="erp-badge erp-badge-slate">{{ ucfirst($record->account_type) }}</span></td>
                            <td class="text-sm text-slate-500">{{ $record->parent?->account_code }}</td>
                            <td class="text-right font-medium">{{ number_format($record->current_balance, 2) }}</td>
                            <td><span class="erp-badge {{ $record->is_active ? 'erp-badge-green' : 'erp-badge-slate' }}">{{ $record->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" onclick="openAccountEdit(@json($record))" class="erp-btn-ghost !px-2.5 !py-2"><x-ui.icon name="pencil" class="h-4 w-4" /></button>
                                <form method="POST" action="{{ route('accounts.destroy', $record) }}" class="inline" onsubmit="return confirm('Delete account?')">
                                    @csrf @method('DELETE')
                                    <button class="erp-btn-danger !px-2.5 !py-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-ui.empty-state title="No accounts" description="Seed the chart of accounts or add your first GL account." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
    </div>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="createOpen = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
            <h3 class="mb-4 text-lg font-bold">Add Account</h3>
            <form method="POST" action="{{ route('accounts.store') }}">@csrf
                <div class="space-y-4">@include('finance.accounts._form')</div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="createOpen = false" class="erp-btn-secondary">Cancel</button>
                    <button class="erp-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editOpen" x-cloak id="accountEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="editOpen = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
            <h3 class="mb-4 text-lg font-bold">Edit Account</h3>
            <form method="POST" id="accountEditForm">@csrf @method('PUT')
                <div id="accountEditFields" class="space-y-4">@include('finance.accounts._form')</div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="editOpen = false" class="erp-btn-secondary">Cancel</button>
                    <button class="erp-btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openAccountEdit(record) {
    const root = document.getElementById('accountEditModal').closest('[x-data]');
    document.getElementById('accountEditForm').action = '/accounts/' + record.id;
    document.querySelectorAll('#accountEditFields [name]').forEach(el => {
        if (record[el.name] !== undefined) el.value = record[el.name] ?? '';
    });
    Alpine.$data(root).editOpen = true;
}
</script>
</x-erp-layout>
