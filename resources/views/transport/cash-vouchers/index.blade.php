@php $title = __('transport.cash_vouchers'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('transport.search_voucher') }}" class="erp-input !mt-0">
        </form>
        <a href="{{ route('transport.cash-vouchers.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> {{ __('transport.new_voucher') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('transport.voucher_no') }}</th>
                <th>{{ __('ui.date') }}</th>
                <th>{{ __('transport.driver') }}</th>
                <th>{{ __('ui.amount') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th class="text-right">{{ __('ui.actions') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->voucher_no }}</td>
                        <td>{{ $row->voucher_date?->format('Y-m-d') }}</td>
                        <td>{{ $row->driver?->name }}</td>
                        <td>{{ number_format($row->total_amount, 2) }}</td>
                        <td><span class="erp-badge {{ $row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-amber' }}">{{ $row->status === 'posted' ? __('ui.posted') : __('ui.draft') }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('transport.cash-vouchers.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state :title="__('transport.empty.vouchers')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
