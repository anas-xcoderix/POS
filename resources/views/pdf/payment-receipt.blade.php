<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.payment_receipt') }} {{ $receipt->receipt_no }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.payment_receipt') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.receipt_no') }}:</strong> {{ $receipt->receipt_no }}</td>
            <td><strong>{{ __('pdf.date') }}:</strong> {{ $receipt->receipt_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.party') }}:</strong>
                {{ $receipt->party_type === 'customer' ? localized($receipt->customer) : localized($receipt->vendor) }}
            </td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ localized($receipt->branch) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.payment_method') }}:</strong> {{ ucfirst($receipt->payment_method) }}</td>
            <td><strong>{{ __('pdf.amount') }}:</strong> {{ number_format($receipt->amount, 2) }}</td>
        </tr>
        @if($receipt->reference_no)
        <tr><td colspan="2"><strong>{{ __('pdf.reference') }}:</strong> {{ $receipt->reference_no }}</td></tr>
        @endif
    </table>

    @if($receipt->remarks)
        <p style="margin-top:24px;"><strong>{{ __('ui.remarks') }}:</strong> {{ $receipt->remarks }}</p>
    @endif

    <p style="margin-top:32px; font-size:11px; color:#64748b;">{{ __('pdf.thank_you') }}</p>
</body>
</html>
