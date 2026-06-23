@php $title = 'Purchase Invoices'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice..." class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('purchase-invoices.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> New Invoice</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Invoice</th><th>Vendor</th><th>Date</th><th>Total</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->invoice_no }}</td>
                        <td>{{ $row->vendor?->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->invoice_date)->format('M d, Y') }}</td>
                        <td class="font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td><span class="erp-badge {{ $row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('documents.purchase-invoice.pdf', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs" target="_blank">PDF</a>
                            @if($row->status !== 'posted')
                                <form method="POST" action="{{ route('purchase-invoices.post', $row) }}" class="inline">@csrf
                                    <button class="erp-btn-primary !py-1.5 !px-3 text-xs">Post</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No purchase invoices" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
