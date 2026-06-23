@php $title = 'Quotation '.$quotation->quotation_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $quotation->quotation_no }}</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $quotation->customer?->name }} · {{ $quotation->branch?->name }}
                </p>
                <p class="text-sm text-slate-500">
                    Date: {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}
                    @if($quotation->valid_until)
                        · Valid until {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}
                    @endif
                </p>
            </div>
            <span class="erp-badge {{ $quotation->status === 'converted' ? 'erp-badge-green' : 'erp-badge-orange' }}">
                {{ ucfirst($quotation->status) }}
            </span>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Part</th><th>Qty</th><th>Unit Price</th><th>Disc %</th><th>VAT %</th><th class="text-right">Line Total</th>
                </tr></thead>
                <tbody>
                    @foreach($quotation->items as $item)
                        <tr>
                            <td>
                                <span class="font-medium">{{ $item->part?->part_number }}</span>
                                <div class="text-xs text-slate-500">{{ Str::limit($item->part?->description_en, 40) }}</div>
                            </td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->discount_percent, 2) }}%</td>
                            <td>{{ number_format($item->vat_percent, 2) }}%</td>
                            <td class="text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50/50">
                    <tr>
                        <td colspan="5" class="text-right font-semibold">Total</td>
                        <td class="text-right text-lg font-bold text-slate-900">{{ number_format($quotation->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($quotation->status !== 'converted')
        <div class="erp-card p-6">
            <h3 class="mb-4 text-base font-bold text-slate-900">Convert to Sales Invoice</h3>
            <form method="POST" action="{{ route('quotations.convert', $quotation) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <x-ui.form-field label="Invoice No" name="invoice_no" :value="$invoiceNo" required />
                <x-ui.form-field label="Invoice Date" name="invoice_date" type="date" :value="date('Y-m-d')" required />
                <x-ui.form-field label="Default Location" name="default_location_id" type="select">
                    <option value="">— Select for all lines —</option>
                    @foreach($locations as $l)
                        <option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>
                    @endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Payment Type" name="invoice_type" type="select">
                    <option value="cash">Cash</option>
                    <option value="credit">Credit</option>
                </x-ui.form-field>
                <x-ui.form-field label="Status" name="status" type="select" hint="Posted will deduct stock immediately">
                    <option value="draft">Draft</option>
                    <option value="posted">Posted — deduct stock</option>
                </x-ui.form-field>
                <div class="flex items-end md:col-span-2 lg:col-span-3">
                    <button type="submit" class="erp-btn-primary">Convert to Invoice</button>
                </div>
            </form>
        </div>
    @endif

    <div>
        <a href="{{ route('quotations.index') }}" class="erp-btn-secondary">Back to Quotations</a>
    </div>
</div>
</x-erp-layout>
