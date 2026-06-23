@php $title = 'Stock Movements'; @endphp
<x-erp-layout>
<div class="erp-card overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="relative flex-1 sm:max-w-sm">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="text" name="search" value="{{ $search }}" placeholder="Part no or reference..." class="erp-input !mt-0 pl-10">
                </div>
                <x-ui.form-field label="Type" name="movement_type" type="select" class="sm:w-48">
                    <option value="">All types</option>
                    @foreach(['purchase_receive','sale_issue','sale_return','transfer_in','transfer_out','adjustment'] as $type)
                        <option value="{{ $type }}" @selected($movementType === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                    @endforeach
                </x-ui.form-field>
                <button class="erp-btn-primary shrink-0">Filter</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('stock.adjustment') }}" class="erp-btn-secondary">Stock Adjustment</a>
                <a href="{{ route('stock.index') }}" class="erp-btn-ghost">Stock Balances</a>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Date</th><th>Type</th><th>Part</th><th>Branch</th><th>Location</th><th>In</th><th>Out</th><th>Balance</th><th>Reference</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="whitespace-nowrap text-sm">{{ $row->movement_date?->format('M d, Y H:i') }}</td>
                        <td><span class="erp-badge erp-badge-slate text-xs">{{ str_replace('_', ' ', $row->movement_type) }}</span></td>
                        <td>
                            <span class="font-medium">{{ $row->part?->part_number }}</span>
                        </td>
                        <td>{{ $row->branch?->code }}</td>
                        <td>{{ $row->location?->code }}</td>
                        <td class="text-emerald-700 font-medium">{{ $row->quantity_in > 0 ? number_format($row->quantity_in, 2) : '—' }}</td>
                        <td class="text-red-600 font-medium">{{ $row->quantity_out > 0 ? number_format($row->quantity_out, 2) : '—' }}</td>
                        <td>{{ number_format($row->balance_after, 2) }}</td>
                        <td class="text-xs text-slate-500">{{ $row->reference_no }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <x-ui.empty-state title="No movements recorded" description="Stock movements appear when you post invoices, returns, transfers, or adjustments." />
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
</div>
</x-erp-layout>
