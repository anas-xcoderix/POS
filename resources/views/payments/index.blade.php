@php $title = 'Payment Receipts'; @endphp
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
                <th>{{ __('pdf.receipt_no') }}</th><th>{{ __('pdf.party') }}</th><th>{{ __('ui.branch') }}</th><th>{{ __('ui.date') }}</th><th>{{ __('pdf.payment_method') }}</th><th class="text-right">{{ __('ui.amount') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('ui.actions') }}</th>
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
                        <td><span class="erp-badge erp-badge-green">{{ __('ui.'.$row->status) }}</span></td>
                        <td><a href="{{ route('documents.payment-receipt.pdf', $row) }}" class="text-sm text-orange-600 hover:underline">{{ __('ui.pdf') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">
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
