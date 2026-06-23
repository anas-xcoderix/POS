@php $title = 'Sales Invoices'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice no..." class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('sales-invoices.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Invoice
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th class="text-right">Action</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold text-slate-900">{{ $row->invoice_no }}</td>
                        <td>{{ $row->customer?->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->invoice_date)->format('M d, Y') }}</td>
                        <td class="font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td>
                            <span class="erp-badge {{ $row->voided_at ? 'erp-badge-red' : ($row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber') }}">
                                {{ $row->voided_at ? 'Voided' : ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('documents.sales-invoice.pdf', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs" target="_blank">PDF</a>
                            @if($row->voided_at)
                                <span class="text-xs text-slate-400">—</span>
                            @elseif($row->status !== 'posted')
                                <form method="POST" action="{{ route('sales-invoices.post', $row) }}" class="inline">
                                    @csrf
                                    <button class="erp-btn-primary !py-1.5 !px-3 text-xs">Post</button>
                                </form>
                            @else
                                <a href="{{ route('sales-invoices.edit-posted', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">Edit</a>
                                <form method="POST" action="{{ route('pick-tickets.create-from-invoice', $row) }}" class="inline">@csrf
                                    <button class="erp-btn-ghost !py-1.5 !px-3 text-xs">Pick</button>
                                </form>
                                <a href="{{ route('sale-returns.create', ['sales_invoice_id' => $row->id]) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">Return</a>
                                <button type="button" onclick="voidInvoice{{ $row->id }}.showModal()" class="erp-btn-danger !py-1.5 !px-3 text-xs">Void</button>
                                <dialog id="voidInvoice{{ $row->id }}" class="rounded-xl p-6 shadow-xl backdrop:bg-slate-900/40">
                                    <form method="POST" action="{{ route('sales-invoices.void', $row) }}" class="space-y-4">
                                        @csrf
                                        <h4 class="font-bold">Void {{ $row->invoice_no }}</h4>
                                        <textarea name="void_reason" required class="erp-input" rows="3" placeholder="Reason for void..."></textarea>
                                        <div class="flex gap-2 justify-end">
                                            <button type="button" onclick="this.closest('dialog').close()" class="erp-btn-secondary">Cancel</button>
                                            <button class="erp-btn-danger">Confirm Void</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <x-ui.empty-state title="No sales invoices" description="Create a sales invoice to bill customers and deduct stock.">
                            <x-slot:action><a href="{{ route('sales-invoices.create') }}" class="erp-btn-primary">Create Invoice</a></x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
