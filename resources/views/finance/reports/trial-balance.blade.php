@php $title = 'Trial Balance'; @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field label="From" name="from" type="date" :value="$from" />
            <x-ui.form-field label="To" name="to" type="date" :value="$to" />
            <button class="erp-btn-primary shrink-0">Run Report</button>
            <a href="{{ route('finance.reports.index') }}" class="erp-btn-secondary shrink-0">Back</a>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Code</th><th>Account</th><th>Type</th><th class="text-right">Debit</th><th class="text-right">Credit</th>
                </tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['account']->account_code }}</td>
                            <td>{{ $row['account']->name }}</td>
                            <td><span class="erp-badge erp-badge-slate">{{ ucfirst($row['account']->account_type) }}</span></td>
                            <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                            <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">No activity in this period.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="bg-slate-50 font-bold">
                        <tr>
                            <td colspan="3" class="text-right">Totals</td>
                            <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-right">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-erp-layout>
