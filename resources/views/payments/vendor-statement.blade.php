@php $title = __('modules.vendor_statement').' — '.localized($vendor); @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $vendor->name }}</h2>
                <p class="text-sm text-slate-500">{{ $vendor->code }} · Balance: {{ number_format($vendor->balance, 2) }}</p>
            </div>
            <a href="{{ route('payments.create', ['party_type' => 'vendor']) }}" class="erp-btn-primary shrink-0">
                <x-ui.icon name="plus" class="h-4 w-4" /> Record Payment
            </a>
        </div>

        <form method="GET" class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-end">
            <x-ui.form-field label="From" name="from" type="date" :value="$from" class="sm:w-44" />
            <x-ui.form-field label="To" name="to" type="date" :value="$to" class="sm:w-44" />
            <button type="submit" class="erp-btn-secondary">Apply Filter</button>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Date</th><th>Type</th><th>Reference</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th>
                </tr></thead>
                <tbody>
                    @php $runningBalance = 0; @endphp
                    @forelse($lines as $line)
                        @php $runningBalance += ($line['credit'] - $line['debit']); @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($line['date'])->format('M d, Y') }}</td>
                            <td><span class="erp-badge erp-badge-slate">{{ $line['type'] }}</span></td>
                            <td class="font-medium">{{ $line['reference'] }}</td>
                            <td class="text-right">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '—' }}</td>
                            <td class="text-right">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '—' }}</td>
                            <td class="text-right font-semibold">{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <x-ui.empty-state title="No transactions" description="No invoices or payments found for this period." />
                        </td></tr>
                    @endforelse
                </tbody>
                @if($lines->isNotEmpty())
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="5" class="text-right font-semibold">Closing Balance</td>
                            <td class="text-right font-bold">{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-erp-layout>
