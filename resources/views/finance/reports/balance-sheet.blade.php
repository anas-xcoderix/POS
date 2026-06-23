@php $title = __('finance.balance_sheet'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field :label="__('finance.as_of')" name="as_of" type="date" :value="$asOf" />
            <button class="erp-btn-primary shrink-0">{{ __('finance.run_report') }}</button>
            <a href="{{ route('finance.reports.index') }}" class="erp-btn-secondary shrink-0">{{ __('finance.back') }}</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        @foreach(['assets' => __('finance.current_assets'), 'liabilities' => __('finance.liabilities'), 'equity' => __('finance.equity')] as $key => $label)
            <div class="erp-card overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 font-bold text-slate-900">{{ $label }}</div>
                <div class="divide-y divide-slate-100">
                    @forelse($$key as $row)
                        <div class="flex justify-between gap-2 px-5 py-3 text-sm">
                            <span class="min-w-0 truncate">{{ $row['account']->account_code }} {{ localized($row['account']) }}</span>
                            <span class="shrink-0 font-medium">{{ number_format($row['balance'], 2) }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-500">{{ __('finance.no_activity') }}</p>
                    @endforelse
                </div>
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-3 flex justify-between font-bold">
                    <span>{{ __('ui.total') }}</span>
                    <span>{{ number_format(collect($$key)->sum('balance'), 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="erp-card p-4 text-sm text-slate-600">
        {{ __('finance.total_assets') }}: <strong>{{ number_format($totalAssets, 2) }}</strong>
        · {{ __('finance.total_liabilities_equity') }}: <strong>{{ number_format($totalLiabilities + $totalEquity, 2) }}</strong>
    </div>
</div>
</x-erp-layout>
