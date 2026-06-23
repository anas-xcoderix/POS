@php $title = $voucher->voucher_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold">{{ $voucher->voucher_no }}</h2>
                <p class="text-sm text-slate-500">{{ $voucher->driver?->name }} · {{ $voucher->voucher_date?->format('Y-m-d') }}</p>
            </div>
            <span class="erp-badge {{ $voucher->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">
                {{ $voucher->status === 'posted' ? __('ui.posted') : __('ui.draft') }}
            </span>
        </div>
        <p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($voucher->total_amount, 2) }}</p>
        @if($voucher->cashBookEntry)
            <p class="mt-2 text-sm text-slate-500">{{ __('transport.posted_to_cashbook') }}: {{ $voucher->cashBookEntry->entry_no }}</p>
        @endif
    </div>

    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('transport.shipment_no') }}</th>
                <th>{{ __('ui.customer') }}</th>
                <th class="text-right">{{ __('ui.amount') }}</th>
            </tr></thead>
            <tbody>
                @foreach($voucher->items as $item)
                    <tr>
                        <td>{{ $item->shipment?->shipment_no }}</td>
                        <td>{{ $item->shipment?->customer?->name }}</td>
                        <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($voucher->status !== 'posted')
        <form method="POST" action="{{ route('transport.cash-vouchers.post', $voucher) }}">
            @csrf
            <button class="erp-btn-primary">{{ __('transport.post_voucher') }}</button>
        </form>
    @endif

    <a href="{{ route('transport.cash-vouchers.index') }}" class="erp-btn-secondary">{{ __('transport.back_to_vouchers') }}</a>
</div>
</x-erp-layout>
