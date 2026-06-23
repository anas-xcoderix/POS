@php $title = __('modules.stock_transfers'); @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative flex-1 sm:max-w-sm">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('pages.search.transfer') }}" class="erp-input !mt-0 pl-10">
        </form>
        <a href="{{ route('stock-transfers.create') }}" class="erp-btn-primary">
            <x-ui.icon name="plus" class="h-4 w-4" /> New Transfer
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Transfer No</th><th>From</th><th>To</th><th>{{ __('ui.date') }}</th><th>{{ __('ui.status') }}</th><th class="text-right">{{ __('pages.table.action') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->transfer_no }}</td>
                        <td>{{ $row->fromBranch?->name }}</td>
                        <td>{{ $row->toBranch?->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->transfer_date)->format('M d, Y') }}</td>
                        <td>
                            <span class="erp-badge {{ $row->status === 'completed' ? 'erp-badge-green' : 'erp-badge-amber' }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($row->status !== 'completed')
                                <form method="POST" action="{{ route('stock-transfers.complete', $row) }}" class="inline">
                                    @csrf
                                    <button class="erp-btn-primary !py-1.5 !px-3 text-xs">Complete Transfer</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Done</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <x-ui.empty-state title="{{ __('pages.empty.stock_transfers') }}" description="{{ __('pages.empty.stock_transfers_hint') }}">
                            <x-slot:action><a href="{{ route('stock-transfers.create') }}" class="erp-btn-primary">Create Transfer</a></x-slot:action>
                        </x-ui.empty-state>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
