@php $title = __('modules.customer_aging'); @endphp
<x-erp-layout>
<div class="mb-4 flex justify-between gap-3">
    <p class="text-sm text-slate-600">Outstanding credit sales by aging bucket (days since invoice date).</p>
    <a href="{{ route('finance.reports.index') }}" class="erp-btn-secondary shrink-0">Back</a>
</div>

<div class="erp-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="erp-table min-w-full">
            <thead><tr>
                <th>{{ __('ui.customer') }}</th><th class="text-right">Current</th><th class="text-right">31-60</th><th class="text-right">61-90</th><th class="text-right">90+</th><th class="text-right">{{ __('ui.total') }}</th>
            </tr></thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td>
                            <span class="font-semibold">{{ $row['customer']?->name }}</span>
                            <div class="text-xs text-slate-500">{{ $row['customer']?->code }}</div>
                        </td>
                        <td class="text-right">{{ number_format($row['buckets']['current'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['buckets']['31_60'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['buckets']['61_90'], 2) }}</td>
                        <td class="text-right text-red-600">{{ number_format($row['buckets']['over_90'], 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.empty-state title="No outstanding receivables" description="Posted credit invoices with balance will appear here." /></td></tr>
                @endforelse
            </tbody>
            @if($records->isNotEmpty())
                @php
                    $totCurrent = $records->sum(fn($r) => $r['buckets']['current']);
                    $tot3160 = $records->sum(fn($r) => $r['buckets']['31_60']);
                    $tot6190 = $records->sum(fn($r) => $r['buckets']['61_90']);
                    $totOver90 = $records->sum(fn($r) => $r['buckets']['over_90']);
                @endphp
                <tfoot class="bg-slate-50 font-bold">
                    <tr>
                        <td class="text-right">Grand Total</td>
                        <td class="text-right">{{ number_format($totCurrent, 2) }}</td>
                        <td class="text-right">{{ number_format($tot3160, 2) }}</td>
                        <td class="text-right">{{ number_format($tot6190, 2) }}</td>
                        <td class="text-right">{{ number_format($totOver90, 2) }}</td>
                        <td class="text-right">{{ number_format($records->sum('total'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
</x-erp-layout>
