<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.part_label') }} {{ $part->part_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; padding: 12px; direction: rtl; }
        .label { border: 1px dashed #94a3b8; padding: 16px; width: 280px; margin: 0 auto; }
        .part-no { font-size: 16px; font-weight: bold; margin-bottom: 4px; direction: ltr; }
        .desc { font-size: 10px; color: #64748b; margin-bottom: 8px; }
        .barcode img { max-width: 100%; height: 50px; }
        .code { font-size: 11px; letter-spacing: 1px; margin-top: 4px; direction: ltr; }
        .price { font-size: 12px; margin-top: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="label">
        <div class="part-no">{{ $part->part_number }}</div>
        <div class="desc">{{ Str::limit(localized($part, 'description_en', 'description_ar'), 50) }}</div>
        <div class="barcode"><img src="data:image/png;base64,{{ $barcode }}" alt="barcode"></div>
        <div class="code">{{ $code }}</div>
        @if($part->list_price > 0)
            <div class="price">{{ number_format($part->list_price, 2) }}</div>
        @endif
    </div>
</body>
</html>
