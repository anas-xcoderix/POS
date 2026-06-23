@php $title = __('modules.quotations'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Search quotation no..." class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('quotations.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Quotation
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Quotation</th><th>Customer</th><th>Date</th><th>Valid Until</th><th>Total</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $row->quotation_no }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->quotation_date)->format('M d, Y') }}</td>
                        <td>{{ $row->valid_until ? \Carbon\Carbon::parse($row->valid_until)->format('M d, Y') : '—' }}</td>
                        <td class="font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td>
                            <span class="erp-badge {{ $row->status === 'converted' ? 'erp-badge-green' : 'erp-badge-orange' }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('quotations.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <x-ui.empty-state title="No quotations" description="Create a quotation to send pricing to customers.">
                            <x-slot:action><a href="{{ route('quotations.create') }}" class="erp-btn-primary">Create Quotation</a></x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
