@php $title = __('hr.payroll'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('payroll.create') }}" class="erp-btn-primary"><x-ui.icon name="plus" class="h-4 w-4" /> {{ __('hr.generate_payroll') }}</a>
    </div>
    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>{{ __('hr.payroll_no') }}</th><th>{{ __('hr.period') }}</th><th>{{ __('ui.branch') }}</th><th>{{ __('ui.status') }}</th><th>{{ __('hr.payment_status') }}</th><th class="text-right">{{ __('ui.total') }}</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-semibold">{{ $row->payroll_no }}</td>
                        <td>{{ $row->periodLabel() }}</td>
                        <td>{{ localized($row->branch) ?? __('hr.all_branches') }}</td>
                        <td><span class="erp-badge {{ $row->status === 'posted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ __('ui.'.$row->status) }}</span></td>
                        <td><span class="erp-badge {{ $row->payment_status === 'paid' ? 'erp-badge-green' : 'erp-badge-slate' }}">{{ __('hr.'.$row->payment_status) }}</span></td>
                        <td class="text-right font-medium">{{ number_format($row->total_amount, 2) }}</td>
                        <td class="text-right"><a href="{{ route('payroll.show', $row) }}" class="erp-btn-ghost !py-1.5 !px-3 text-xs">{{ __('ui.view') ?? 'View' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state :title="__('hr.no_payroll_runs')" :description="__('hr.generate_hint')" /></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($records->hasPages())<div class="border-t px-5 py-4">{{ $records->links() }}</div>@endif
    </div>
</div>
</x-erp-layout>
