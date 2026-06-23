@php $title = __('modules.payments'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="Search receipt no..." class="erp-input !mt-0 pl-10">
        </form>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('payments.create', ['party_type' => 'customer']) }}" class="erp-btn-primary">
                <x-ui.icon name="plus" class="h-4 w-4" /> Customer Receipt
            </a>
            <a href="{{ route('payments.create', ['party_type' => 'vendor']) }}" class="erp-btn-secondary">
                <x-ui.icon name="plus" class="h-4 w-4" /> Vendor Payment
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Receipt No</th><th>Party</th><th>Branch</th><th>Date</th><th>Method</th><th class="text-right">Amount</th><th>Status</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->receipt_no }}</td>
                        <td>
                            @if($row->party_type === 'customer')
                                <span class="erp-badge erp-badge-slate">Customer</span>
                                <div class="mt-0.5 text-sm">{{ $row->customer?->name }}</div>
                            @else
                                <span class="erp-badge erp-badge-slate">Vendor</span>
                                <div class="mt-0.5 text-sm">{{ $row->vendor?->name }}</div>
                            @endif
                        </td>
                        <td>{{ $row->branch?->code }}</td>
                        <td>{{ $row->receipt_date?->format('M d, Y') }}</td>
                        <td>{{ ucfirst($row->payment_method) }}</td>
                        <td class="text-right font-medium">{{ number_format($row->amount, 2) }}</td>
                        <td><span class="erp-badge erp-badge-green">{{ ucfirst($row->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <x-ui.empty-state title="No payment receipts" description="Record customer receipts or vendor payments.">
                            <x-slot:action>
                                <a href="{{ route('payments.create', ['party_type' => 'customer']) }}" class="erp-btn-primary">Record Payment</a>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
