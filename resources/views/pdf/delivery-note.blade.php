<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.delivery_note') }} {{ $note->dn_no }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>{{ __('pdf.delivery_note') }}</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>{{ __('pdf.dn_no') }}:</strong> {{ $note->dn_no }}</td>
            <td><strong>{{ __('pdf.date') }}:</strong> {{ $note->delivery_date?->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('pdf.customer') }}:</strong> {{ localized($note->customer) }}</td>
            <td><strong>{{ __('pdf.branch') }}:</strong> {{ localized($note->branch) }}</td>
        </tr>
        @if($note->driver_name || $note->vehicle_plate)
        <tr>
            @if($note->driver_name)<td><strong>{{ __('pdf.driver') }}:</strong> {{ $note->driver_name }}</td>@endif
            @if($note->vehicle_plate)<td><strong>{{ __('pdf.vehicle_plate') }}:</strong> {{ $note->vehicle_plate }}</td>@endif
        </tr>
        @endif
        @if($note->salesInvoice)
        <tr><td colspan="2"><strong>{{ __('pdf.invoice_no') }}:</strong> {{ $note->salesInvoice->invoice_no }}</td></tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('pdf.part_no') }}</th>
                <th>{{ __('pdf.description') }}</th>
                <th class="text-right">{{ __('pdf.qty') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($note->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->part?->part_number }}</td>
                    <td>{{ localized($item->part, 'description_en', 'description_ar') }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($note->remarks)
        <p style="margin-top:24px;"><strong>{{ __('ui.remarks') }}:</strong> {{ $note->remarks }}</p>
    @endif
</body>
</html>
