<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; @if(is_rtl()) direction: rtl; text-align: right; @endif }
    .header { margin-bottom: 24px; border-bottom: 2px solid #ea580c; padding-bottom: 12px; }
    .title { font-size: 20px; font-weight: bold; color: #ea580c; }
    .meta td { padding: 2px 16px 2px 0; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
    table.items th, table.items td { border: 1px solid #e2e8f0; padding: 8px; text-align: {{ is_rtl() ? 'right' : 'left' }}; }
    table.items th { background: #fff7ed; }
    .text-right { text-align: {{ is_rtl() ? 'left' : 'right' }}; direction: ltr; }
    .total { margin-top: 16px; font-size: 14px; font-weight: bold; text-align: {{ is_rtl() ? 'left' : 'right' }}; }
</style>
