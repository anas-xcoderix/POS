@php $title = __('pos.session_report'); @endphp
<x-pos-layout :title="$title">
<div class="mx-auto max-w-2xl p-6">
    <div class="erp-card p-6 space-y-6">
        <div class="text-center">
            <h1 class="text-xl font-bold text-slate-900">{{ config('app.name') }}</h1>
            <p class="text-sm text-slate-500">{{ __('pos.session_report') }}</p>
        </div>

        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500">{{ __('pos.terminal') }}</dt><dd class="font-medium">{{ $session->posTerminal?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('pos.session') }}</dt><dd class="font-medium">{{ $session->session_no }}</dd></div>
            <div><dt class="text-slate-500">{{ __('pos.cashier') }}</dt><dd>{{ $session->user?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('pos.opened_at') }}</dt><dd>{{ $session->opened_at?->format('Y-m-d H:i') }}</dd></div>
            @if($session->closed_at)
                <div><dt class="text-slate-500">{{ __('pos.close_session') }}</dt><dd>{{ $session->closed_at?->format('Y-m-d H:i') }}</dd></div>
            @endif
        </dl>

        <div class="border-t border-slate-100 pt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span>{{ __('pos.opening_float') }}</span><span>{{ number_format($session->opening_float, 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('pos.invoice_count') }}</span><span>{{ $stats['invoice_count'] }}</span></div>
            <div class="flex justify-between"><span>{{ __('pos.cash_sales') }}</span><span>{{ number_format($stats['cash_sales'], 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('pos.credit_sales') }}</span><span>{{ number_format($stats['credit_sales'], 2) }}</span></div>
            <div class="flex justify-between font-bold text-base border-t border-slate-100 pt-2"><span>{{ __('pos.total_sales') }}</span><span>{{ number_format($stats['total_sales'], 2) }}</span></div>
            <div class="flex justify-between"><span>{{ __('pos.expected_cash') }}</span><span class="font-bold">{{ number_format($stats['expected_cash'], 2) }}</span></div>
            @if($session->closing_float !== null)
                <div class="flex justify-between"><span>{{ __('pos.closing_float') }}</span><span>{{ number_format($session->closing_float, 2) }}</span></div>
                <div class="flex justify-between"><span>{{ __('pos.cash_variance') }}</span><span>{{ number_format($session->closing_float - $stats['expected_cash'], 2) }}</span></div>
            @endif
        </div>

        @if($stats['recent']->isNotEmpty())
            <table class="erp-table min-w-full text-sm">
                <thead><tr><th>{{ __('pdf.invoice_no') }}</th><th>{{ __('pos.customer') }}</th><th>{{ __('pos.payment_type') }}</th><th class="text-right">{{ __('pos.total') }}</th></tr></thead>
                <tbody>
                    @foreach($stats['recent'] as $inv)
                        <tr>
                            <td>{{ $inv->invoice_no }}</td>
                            <td>{{ localized($inv->customer) }}</td>
                            <td>{{ $inv->invoice_type === 'credit' ? __('pos.credit') : __('pos.cash') }}</td>
                            <td class="text-right">{{ number_format($inv->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="flex gap-3 print:hidden">
            <button onclick="window.print()" class="erp-btn-primary">{{ __('ui.pdf') }} / Print</button>
            @if($session->status === 'open')
                <a href="{{ route('pos.counter', $session) }}" class="erp-btn-secondary">{{ __('pos.counter') }}</a>
            @else
                <a href="{{ route('pos.index') }}" class="erp-btn-secondary">{{ __('pos.back_terminals') }}</a>
            @endif
        </div>
    </div>
</div>
<style>@media print { body { background: white; } .erp-card { box-shadow: none; } }</style>
</x-pos-layout>
