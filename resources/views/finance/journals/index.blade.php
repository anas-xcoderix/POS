@php $title = 'Journal Entries'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <form method="GET" class="relative max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Entry no or description..." class="erp-input !mt-0 pl-10">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead><tr>
                <th>Entry No</th><th>Date</th><th>Branch</th><th>Description</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->entry_no }}</td>
                        <td>{{ $row->entry_date?->format('M d, Y') }}</td>
                        <td>{{ $row->branch?->code }}</td>
                        <td class="max-w-xs truncate">{{ $row->description }}</td>
                        <td><span class="erp-badge erp-badge-green">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('journal-entries.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No journal entries" description="Entries are created automatically when you post sales or purchase invoices." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
