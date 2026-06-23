@php $title = $proforma->proforma_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">{{ $proforma->proforma_no }}</h2>
                <p class="text-sm text-slate-500">{{ $proforma->customer?->name }} · {{ $proforma->branch?->name }}</p>
            </div>
            <span class="erp-badge {{ $proforma->status === 'converted' ? 'erp-badge-green' : 'erp-badge-orange' }}">{{ ucfirst($proforma->status) }}</span>
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $proforma->proforma_date?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">Total</dt><dd class="font-medium">{{ number_format($proforma->total_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">Currency</dt><dd class="font-medium">{{ $proforma->currency?->code ?? 'SAR' }}</dd></div>
            <div><dt class="text-slate-500">Valid Until</dt><dd class="font-medium">{{ $proforma->valid_until?->format('Y-m-d') ?? '—' }}</dd></div>
        </dl>
    </div>
    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr><th>Part</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($proforma->items as $item)
                    <tr>
                        <td>{{ $item->part?->part_number }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($proforma->status !== 'converted')
        <div class="erp-card p-6">
            <h3 class="mb-4 font-bold">Convert to Sales Invoice</h3>
            <form method="POST" action="{{ route('proforma-invoices.convert', $proforma) }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                @csrf
                <x-ui.form-field label="Invoice No" name="invoice_no" :value="$invoiceNo" required />
                <x-ui.form-field label="Invoice Date" name="invoice_date" type="date" :value="date('Y-m-d')" required />
                <x-ui.form-field label="Type" name="invoice_type" type="select"><option value="cash">Cash</option><option value="credit">Credit</option></x-ui.form-field>
                <x-ui.form-field label="Status" name="status" type="select"><option value="posted">Posted</option><option value="draft">Draft</option></x-ui.form-field>
                <div class="md:col-span-4"><button class="erp-btn-primary">Convert to Invoice</button></div>
            </form>
        </div>
    @endif
</div>
</x-erp-layout>
