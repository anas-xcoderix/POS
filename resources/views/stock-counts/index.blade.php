@php $title = __('modules.stock_counts'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Search count no..." class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('stock-counts.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Count
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Count No</th><th>Branch</th><th>Location</th><th>Date</th><th>Items</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->count_no }}</td>
                        <td>{{ $row->branch?->name }}</td>
                        <td>{{ $row->location?->code ?? 'All locations' }}</td>
                        <td>{{ $row->count_date?->format('M d, Y') }}</td>
                        <td>{{ $row->items?->count() ?? 0 }}</td>
                        <td>
                            <span class="erp-badge {{ $row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('stock-counts.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <x-ui.empty-state title="No stock counts" description="Start a physical count session to reconcile inventory.">
                            <x-slot:action><a href="{{ route('stock-counts.create') }}" class="erp-btn-primary">Start Count</a></x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
