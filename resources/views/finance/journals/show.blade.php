@php $title = 'Journal '.$entry->entry_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $entry->entry_no }}</h2>
                <p class="text-sm text-slate-500">{{ $entry->entry_date?->format('M d, Y') }} · {{ $entry->branch?->name }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $entry->description }}</p>
            </div>
            <span class="erp-badge erp-badge-green">{{ ucfirst($entry->status) }}</span>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>Account</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th>
                </tr></thead>
                <tbody>
                    @foreach($entry->lines as $line)
                        <tr>
                            <td>
                                <span class="font-medium">{{ $line->account?->account_code }}</span>
                                <div class="text-xs text-slate-500">{{ $line->account?->name }}</div>
                            </td>
                            <td class="text-sm text-slate-500">{{ $line->description }}</td>
                            <td class="text-right font-medium text-slate-800">{{ $line->debit > 0 ? number_format($line->debit, 2) : '—' }}</td>
                            <td class="text-right font-medium text-slate-800">{{ $line->credit > 0 ? number_format($line->credit, 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="2" class="font-semibold text-right">Totals</td>
                        <td class="text-right font-bold">{{ number_format($entry->lines->sum('debit'), 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($entry->lines->sum('credit'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <a href="{{ route('journal-entries.index') }}" class="erp-btn-secondary">Back to Journals</a>
</div>
</x-erp-layout>
