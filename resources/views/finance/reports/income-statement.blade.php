@php $title = 'Income Statement'; @endphp
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

    <div class="erp-card p-6 max-w-xl">
        <h3 class="mb-4 text-lg font-bold text-slate-900">Profit & Loss</h3>
        <p class="mb-6 text-sm text-slate-500">{{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
        <dl class="space-y-4">
            <div class="flex justify-between border-b border-slate-100 pb-3">
                <dt class="text-slate-600">Total Revenue</dt>
                <dd class="font-semibold text-emerald-600">{{ number_format($revenue, 2) }}</dd>
            </div>
            <div class="flex justify-between border-b border-slate-100 pb-3">
                <dt class="text-slate-600">Total Expenses</dt>
                <dd class="font-semibold text-red-600">{{ number_format($expenses, 2) }}</dd>
            </div>
            <div class="flex justify-between pt-2">
                <dt class="text-lg font-bold text-slate-900">Net Income</dt>
                <dd class="text-lg font-bold {{ $netIncome >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($netIncome, 2) }}</dd>
            </div>
        </dl>
    </div>
</div>
</x-erp-layout>
