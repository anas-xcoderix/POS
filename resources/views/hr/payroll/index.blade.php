@php $title = 'Payroll'; @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('payroll.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> Generate Payroll</a>
    </div>
    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Payroll No</th><th>Period</th><th>Branch</th><th>Status</th><th class="text-right">Total</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->payroll_no }}</td>
                        <td>{{ $row->periodLabel() }}</td>
                        <td>{{ $row->branch?->name ?? 'All' }}</td>
                        <td><span class="erp-badge {{ $row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($row->status) }}</span></td>
                        <td class="text-right font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td class="text-right"><a href="{{ route('payroll.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No payroll runs" description="Generate payroll for a month." /></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($records->hasPages())<div class="border-t px-5 py-4">{{ $records->links() }}</div>@endif
    </div>
</div>
</x-erp-layout>
