<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pos.receipt') }} {{ $salesInvoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; max-width: 280px; margin: 0 auto; @if(is_rtl()) direction: rtl; text-align: right; @endif }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #cbd5e1; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .num { text-align: {{ is_rtl() ? 'left' : 'right' }}; white-space: nowrap; }
        .total { font-size: 13px; font-weight: bold; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="center bold" style="font-size:14px;">{{ config('app.name') }}</div>
    <div class="center">{{ __('pos.receipt') }}</div>
    <div class="divider"></div>
    <table>
        <tr><td>{{ __('pdf.invoice_no') }}</td><td class="num">{{ $salesInvoice->invoice_no }}</td></tr>
        <tr><td>{{ __('pdf.date') }}</td><td class="num">{{ $salesInvoice->invoice_date->format('Y-m-d H:i') }}</td></tr>
        <tr><td>{{ __('pos.customer') }}</td><td class="num">{{ localized($salesInvoice->customer) }}</td></tr>
        <tr><td>{{ __('pos.payment_type') }}</td><td class="num">{{ $salesInvoice->invoice_type === 'credit' ? __('pos.credit') : __('pos.cash') }}</td></tr>
    </table>
    <div class="divider"></div>
    <table>
        @foreach($salesInvoice->items as $item)
            <tr>
                <td colspan="2" class="bold">{{ $item->part?->part_number }}</td>
            </tr>
            <tr>
                <td>{{ Str::limit(localized($item->part, 'description_en', 'description_ar'), 28) }}</td>
                <td></td>
            </tr>
            <tr>
                <td>{{ number_format($item->quantity, 2) }} × {{ number_format($item->unit_price, 2) }}</td>
                <td class="num">{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </table>
    <div class="divider"></div>
    <table>
        <tr><td>{{ __('pos.subtotal') }}</td><td class="num">{{ number_format($salesInvoice->subtotal, 2) }}</td></tr>
        <tr><td>{{ __('pos.vat') }}</td><td class="num">{{ number_format($salesInvoice->vat_amount, 2) }}</td></tr>
        <tr class="total"><td>{{ __('pos.total') }}</td><td class="num">{{ number_format($salesInvoice->total_amount, 2) }}</td></tr>
        @if($salesInvoice->invoice_type === 'cash' && $salesInvoice->paid_amount > 0)
            <tr><td>{{ __('pos.paid_amount') }}</td><td class="num">{{ number_format($salesInvoice->paid_amount, 2) }}</td></tr>
            <tr><td>{{ __('pos.change') }}</td><td class="num">{{ number_format(max(0, $salesInvoice->paid_amount - $salesInvoice->total_amount), 2) }}</td></tr>
        @endif
    </table>
    <div class="divider"></div>
    <div class="center" style="margin-top:12px;">{{ __('pos.thank_you') }}</div>
</body>
</html>
