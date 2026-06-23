<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.quotation') }} {{ $quotation->quotation_no }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.quotation') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.document_no') }}:</strong> {{ $quotation->quotation_no }}</td>
            <td><strong>{{ __('pdf.date') }}:</strong> {{ $quotation->quotation_date }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.customer') }}:</strong> {{ localized($quotation->customer) }}</td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ localized($quotation->branch) }}</td>
        </tr>
        @if($quotation->valid_until)
        <tr><td colspan="2"><strong>{{ __('pdf.valid_until') }}:</strong> {{ $quotation->valid_until }}</td></tr>
        @endif
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
            @foreach($quotation->items as $i => $item)
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
        {{ __('pdf.subtotal') }}: {{ number_format($quotation->subtotal, 2) }}<br>
        {{ __('pdf.vat') }}: {{ number_format($quotation->vat_amount, 2) }}<br>
        <strong>{{ __('pdf.total') }}: {{ number_format($quotation->total_amount, 2) }}</strong>
    </div>

    @if($quotation->remarks)
        <p style="margin-top:24px;"><strong>{{ __('ui.remarks') }}:</strong> {{ $quotation->remarks }}</p>
    @endif
</body>
</html>
