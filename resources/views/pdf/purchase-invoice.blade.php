<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice {{ $purchaseInvoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #7c3aed; padding-bottom: 12px; }
        .title { font-size: 20px; font-weight: bold; color: #7c3aed; }
        .meta { margin-top: 8px; }
        .meta td { padding: 2px 16px 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th, table.items td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        table.items th { background: #f1f5f9; }
        .text-right { text-align: right; }
        .total { margin-top: 16px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name') }}</div>
        <div>Purchase Invoice</div>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Invoice No:</strong> {{ $purchaseInvoice->invoice_no }}</td>
            <td><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseInvoice->invoice_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><strong>Vendor:</strong> {{ $purchaseInvoice->vendor?->name }}</td>
            <td><strong>Branch:</strong> {{ $purchaseInvoice->branch?->name }}</td>
        </tr>
        @if($purchaseInvoice->vendor_invoice_no)
            <tr><td><strong>Vendor Ref:</strong> {{ $purchaseInvoice->vendor_invoice_no }}</td></tr>
        @endif
        @if($purchaseInvoice->purchaseOrder)
            <tr><td><strong>PO No:</strong> {{ $purchaseInvoice->purchaseOrder->po_no }}</td></tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Part No</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseInvoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->part?->part_number }}</td>
                    <td>{{ $item->part?->description_en }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total text-right">
        Subtotal: {{ number_format($purchaseInvoice->subtotal, 2) }}<br>
        VAT: {{ number_format($purchaseInvoice->vat_amount, 2) }}<br>
        <strong>Total: {{ number_format($purchaseInvoice->total_amount, 2) }}</strong>
    </div>
</body>
</html>
