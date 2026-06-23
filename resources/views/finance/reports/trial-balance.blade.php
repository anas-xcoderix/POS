@php $title = __('finance.trial_balance'); @endphp
<x-erp-layout>
<div class="space-y-4">
    <div class="erp-card p-4">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <x-ui.form-field :label="__('ui.from')" name="from" type="date" :value="$from" />
            <x-ui.form-field :label="__('ui.to')" name="to" type="date" :value="$to" />
            <button class="erp-btn-primary shrink-0">{{ __('finance.run_report') }}</button>
            <a href="{{ route('finance.reports.index') }}" class="erp-btn-secondary shrink-0">{{ __('finance.back') }}</a>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead><tr>
                    <th>{{ __('finance.code') }}</th><th>{{ __('finance.account') }}</th><th>{{ __('finance.type') }}</th><th class="text-right">{{ __('finance.debit') }}</th><th class="text-right">{{ __('finance.credit') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['account']->account_code }}</td>
                            <td>{{ localized($row['account']) }}</td>
                            <td><span class="erp-badge erp-badge-slate">{{ ucfirst($row['account']->account_type) }}</span></td>
                            <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                            <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('finance.no_activity') }}</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="bg-slate-50 font-bold">
                        <tr>
                            <td colspan="3" class="text-right">{{ __('finance.totals') }}</td>
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
