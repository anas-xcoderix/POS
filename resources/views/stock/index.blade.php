@php $title = __('modules.stock'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route('stock.movements') }}" class="erp-btn-secondary text-sm">Movement History</a>
            <a href="{{ route('stock.adjustment') }}" class="erp-btn-secondary text-sm">Stock Adjustment</a>
            <a href="{{ route('stock-transfers.index') }}" class="erp-btn-secondary text-sm">Transfers</a>
        </div>
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="relative flex-1 sm:max-w-sm">
                <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by part..." class="erp-input !mt-0 pl-10">
            </div>
            <x-ui.form-field label="Branch" name="branch_id" type="select" class="sm:w-48">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </x-ui.form-field>
            <button class="erp-btn-primary shrink-0">Apply Filters</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Part No</th><th>Description</th><th>Branch</th><th>Location</th><th>Quantity</th><th>Avg Cost</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $row->part?->part_number }}</td>
                        <td class="max-w-xs truncate">{{ $row->part?->description_en }}</td>
                        <td>{{ $row->branch?->name }}</td>
                        <td><span class="erp-badge erp-badge-slate">{{ $row->location?->code }}</span></td>
                        <td><span class="font-semibold text-emerald-700">{{ number_format($row->quantity, 2) }}</span></td>
                        <td>{{ number_format($row->avg_cost, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No stock on hand" description="Receive purchase invoices to add stock to your locations." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
