@php $title = 'Fixed Assets'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search asset..." class="erp-input !mt-0">
        </form>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('deprModal').showModal()" class="erp-btn-secondary">Run Depreciation</button>
            <a href="{{ route('fixed-assets.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> Register Asset</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Code</th><th>Name</th><th>Category</th><th>Purchase Value</th><th>NBV</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->asset_code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->category?->name }}</td>
                        <td>{{ number_format($row->purchase_value, 2) }}</td>
                        <td>{{ number_format($row->net_book_value, 2) }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('fixed-assets.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state title="No fixed assets" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>

<dialog id="deprModal" class="rounded-xl p-6 shadow-xl backdrop:bg-slate-900/40">
    <form method="POST" action="{{ route('fixed-assets.depreciate') }}" class="space-y-4">
        @csrf
        <h4 class="font-bold">Run Monthly Depreciation</h4>
        <x-ui.form-field label="Year" name="year" type="number" :value="date('Y')" required />
        <x-ui.form-field label="Month" name="month" type="number" min="1" max="12" :value="date('n')" required />
        <div class="flex justify-end gap-2">
            <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">Cancel</button>
            <button class="erp-btn-primary">Run</button>
        </div>
    </form>
</dialog>
</x-erp-layout>
