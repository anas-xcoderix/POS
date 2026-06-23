@php $title = 'Proforma Invoices'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search proforma no..." class="erp-input !mt-0">
        </form>
        <a href="{{ route('proforma-invoices.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> New Proforma</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Proforma</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->proforma_no }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ $row->proforma_date?->format('M d, Y') }}</td>
                        <td>{{ number_format($row->total_amount, 2) }}</td>
                        <td><span class="erp-badge {{ $row->status === 'converted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right"><a href="{{ route('proforma-invoices.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No proforma invoices" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
