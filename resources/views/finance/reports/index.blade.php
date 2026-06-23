@php $title = __('finance.finance_reports'); @endphp
<x-erp-layout>
<div class="mb-4">
    <a href="{{ route('reports.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700">← {{ __('nav.reports') }}</a>
</div>
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach([
        [__('finance.trial_balance'), __('finance.trial_balance'), route('finance.reports.trial-balance'), 'document'],
        [__('finance.income_statement'), __('finance.income_statement'), route('finance.reports.income-statement'), 'document'],
        [__('finance.balance_sheet'), __('finance.balance_sheet'), route('finance.reports.balance-sheet'), 'building'],
        [__('finance.customer_aging'), __('finance.customer_aging'), route('finance.reports.customer-aging'), 'users'],
        [__('finance.vendor_aging'), __('finance.vendor_aging'), route('finance.reports.vendor-aging'), 'truck'],
        [__('nav.journal_entries'), __('nav.journal_entries'), route('journal-entries.index'), 'document'],
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
