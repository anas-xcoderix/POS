<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.purchase_order') }} {{ $order->po_no }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.purchase_order') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.po_no') }}:</strong> {{ $order->po_no }}</td>
            <td><strong>{{ __('pdf.date') }}:</strong> {{ $order->po_date }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.vendor') }}:</strong> {{ localized($order->vendor) }}</td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ localized($order->branch) }}</td>
        </tr>
        @if($order->expected_date)
        <tr><td colspan="2"><strong>{{ __('pdf.expected_date') }}:</strong> {{ $order->expected_date }}</td></tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('pdf.part_no') }}</th>
                <th>{{ __('pdf.description') }}</th>
                <th class="text-right">{{ __('pdf.ordered') }}</th>
                <th class="text-right">{{ __('pdf.unit_price') }}</th>
                <th class="text-right">{{ __('pdf.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
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
        {{ __('pdf.subtotal') }}: {{ number_format($order->subtotal, 2) }}<br>
        {{ __('pdf.vat') }}: {{ number_format($order->vat_amount, 2) }}<br>
        <strong>{{ __('pdf.total') }}: {{ number_format($order->total_amount, 2) }}</strong>
    </div>

    @if($order->remarks)
        <p style="margin-top:24px;"><strong>{{ __('ui.remarks') }}:</strong> {{ $order->remarks }}</p>
    @endif
</body>
</html>
