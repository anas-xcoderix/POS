@php $title = 'Finance Reports'; @endphp
<x-erp-layout>
<div class="mb-4">
    <a href="{{ route('reports.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">← All Reports Center (30 reports)</a>
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach([
        ['Trial Balance', 'Account balances for a period', route('reports.show', 'trial-balance'), 'document'],
        ['Income Statement', 'Revenue vs expenses (P&L)', route('reports.show', 'income-statement'), 'document'],
        ['Balance Sheet', 'Assets, liabilities & equity', route('reports.show', 'balance-sheet'), 'building'],
        ['Customer Aging', 'Outstanding receivables by age', route('reports.show', 'customer-aging'), 'users'],
        ['Vendor Aging', 'Outstanding payables by age', route('reports.show', 'vendor-aging'), 'truck'],
        ['Journal Entries', 'View all GL postings', route('journal-entries.index'), 'document'],
    ] as [$label, $desc, $url, $icon])
        <a href="{{ $url }}" class="erp-quick-link group">
            <div class="flex items-start gap-3 min-w-0">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                    <x-ui.icon :name="$icon" class="h-5 w-5 text-orange-500" />
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-800">{{ $label }}</p>
                    <p class="text-sm text-slate-500">{{ $desc }}</p>
                </div>
            </div>
            <svg class="h-5 w-5 shrink-0 text-slate-300 group-hover:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endforeach
</div>
</x-erp-layout>
