<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.payslip') }} {{ $item->employee?->name }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.payslip') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.employee') }}:</strong> {{ localized($item->employee) }}</td>
            <td><strong>{{ __('pdf.employee_no') }}:</strong> {{ $item->employee?->employee_no }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.period') }}:</strong> {{ $run->periodLabel() }}</td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ $run->branch ? localized($run->branch) : __('hr.all_branches') }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('hr.days_present') }}:</strong> {{ $item->days_present }}</td>
            <td><strong>{{ __('hr.days_absent') }}:</strong> {{ $item->days_absent }}</td>
        </tr>
        @if($item->employee?->bank_account)
        <tr><td colspan="2"><strong>{{ __('hr.bank_account') }}:</strong> {{ $item->employee->bank_name }} — {{ $item->employee->bank_account }}</td></tr>
        @endif
    </table>

    <table class="items">
        <thead><tr><th>{{ __('finance.description') }}</th><th class="text-right">{{ __('finance.amount') }}</th></tr></thead>
        <tbody>
            <tr><td>{{ __('hr.basic') }}</td><td class="text-right">{{ number_format($item->basic_salary, 2) }}</td></tr>
            <tr><td>{{ __('pdf.housing_allowance') }}</td><td class="text-right">{{ number_format($item->housing_allowance, 2) }}</td></tr>
            <tr><td>{{ __('pdf.transport_allowance') }}</td><td class="text-right">{{ number_format($item->transport_allowance, 2) }}</td></tr>
            @if($item->overtime_amount > 0)
            <tr><td>{{ __('hr.overtime') }}</td><td class="text-right">{{ number_format($item->overtime_amount, 2) }}</td></tr>
            @endif
            @if($item->bonus_amount > 0)
            <tr><td>{{ __('hr.bonus') }}</td><td class="text-right">{{ number_format($item->bonus_amount, 2) }}</td></tr>
            @endif
            @if($item->deductions > 0)
            <tr><td>{{ __('hr.absent_deduction') }}</td><td class="text-right">({{ number_format($item->deductions, 2) }})</td></tr>
            @endif
            @if($item->gosi_deduction > 0)
            <tr><td>{{ __('hr.gosi') }}</td><td class="text-right">({{ number_format($item->gosi_deduction, 2) }})</td></tr>
            @endif
            @if($item->loan_deduction > 0)
            <tr><td>{{ __('hr.loan') }}</td><td class="text-right">({{ number_format($item->loan_deduction, 2) }})</td></tr>
            @endif
            @if($item->other_deductions > 0)
            <tr><td>{{ __('hr.other_deductions') }}</td><td class="text-right">({{ number_format($item->other_deductions, 2) }})</td></tr>
            @endif
        </tbody>
    </table>

    <div class="total text-right">
        <strong>{{ __('pdf.net_salary') }}: {{ number_format($item->net_pay, 2) }}</strong>
    </div>
    @if($item->notes)
        <p style="margin-top:16px;font-size:11px;"><strong>{{ __('hr.notes') }}:</strong> {{ $item->notes }}</p>
    @endif
</body>
</html>
