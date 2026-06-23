@php $title = __('nav.pick_tickets'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-wrap gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('pages.search.pick') }}" class="erp-input !mt-0 sm:max-w-xs">
            <select name="status" class="erp-input !mt-0 sm:max-w-[160px]" onchange="this.form.submit()">
                <option value="">{{ __('pages.filter.all_statuses') }}</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="picked" @selected($status === 'picked')>Picked</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Pick No</th><th>{{ __('pages.table.invoice') }}</th><th>{{ __('ui.customer') }}</th><th>Location</th><th>{{ __('ui.status') }}</th><th class="text-right">{{ __('pages.table.action') }}</th>
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
                    <tr><td colspan="6"><x-ui.empty-state title="{{ __('pages.empty.pick_tickets') }}" description="{{ __('pages.empty.pick_tickets_hint') }}" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
