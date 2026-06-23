<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.sales_invoice') }} {{ $salesInvoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #ea580c; padding-bottom: 12px; }
        .title { font-size: 20px; font-weight: bold; color: #ea580c; }
        .meta td { padding: 2px 16px 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th, table.items td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        table.items th { background: #fff7ed; }
        .text-right { text-align: right; }
        .total { margin-top: 16px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.sales_invoice') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.invoice_no') }}:</strong> {{ $salesInvoice->invoice_no }}</td>
            <td><strong>{{ __('pdf.date') }}:</strong> {{ $salesInvoice->invoice_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.customer') }}:</strong> {{ localized($salesInvoice->customer) }}</td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ localized($salesInvoice->branch) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.payment') }}:</strong> {{ $salesInvoice->invoice_type === 'credit' ? __('pdf.credit') : __('pdf.cash') }}</td>
            <td><strong>{{ __('ui.status') }}:</strong> {{ __('ui.'.$salesInvoice->status) }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('pdf.part_no') }}</th>
                <th>{{ __('pdf.description') }}</th>
                <th class="text-right">{{ __('pdf.qty') }}</th>
                <th class="text-right">{{ __('pdf.unit_price') }}</th>
                <th class="text-right">{{ __('pdf.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesInvoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->part?->part_number }}</td>
                    <td>{{ localized($item->part, 'description_en', 'description_ar') }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total text-right">
        {{ __('pdf.subtotal') }}: {{ number_format($salesInvoice->subtotal, 2) }}<br>
        {{ __('pdf.vat') }}: {{ number_format($salesInvoice->vat_amount, 2) }}<br>
        <strong>{{ __('pdf.total') }}: {{ number_format($salesInvoice->total_amount, 2) }}</strong>
    </div>

    @if($salesInvoice->remarks)
        <p style="margin-top:24px;"><strong>{{ __('ui.remarks') }}:</strong> {{ $salesInvoice->remarks }}</p>
    @endif
</body>
</html>
