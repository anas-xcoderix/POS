@php $title = 'Pick Tickets'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search pick no..." class="erp-input !mt-0 sm:max-w-xs">
            <select name="status" class="erp-input !mt-0 sm:max-w-[160px]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="picked" @selected($status === 'picked')>Picked</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Pick No</th><th>Invoice</th><th>Customer</th><th>Location</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->pick_no }}</td>
                        <td>{{ $row->salesInvoice?->invoice_no }}</td>
                        <td>{{ $row->salesInvoice?->customer?->name }}</td>
                        <td>{{ $row->location?->code }}</td>
                        <td><span class="erp-badge {{ $row->status === 'picked' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('pick-tickets.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No pick tickets" description="Create a pick ticket from a posted sales invoice." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
