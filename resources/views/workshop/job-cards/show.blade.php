@php $title = __('modules.job_card').' '.$jobCard->job_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $jobCard->job_no }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $jobCard->customer?->name }} · {{ $jobCard->branch?->name }}</p>
                <p class="text-sm text-slate-500">
                    @if($jobCard->vehicle) Vehicle: {{ $jobCard->vehicle->plate_no }} ({{ $jobCard->vehicle->make }} {{ $jobCard->vehicle->model }}) · @endif
                    Mechanic: {{ $jobCard->mechanic?->name ?? 'Unassigned' }}
                </p>
                @if($jobCard->complaint)<p class="mt-2 text-sm"><strong>Complaint:</strong> {{ $jobCard->complaint }}</p>@endif
            </div>
            <span class="erp-badge erp-badge-orange">{{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="erp-card p-4"><p class="text-xs text-slate-500">Parts</p><p class="text-xl font-bold">{{ number_format($jobCard->parts_total, 2) }}</p></div>
        <div class="erp-card p-4"><p class="text-xs text-slate-500">Labor</p><p class="text-xl font-bold">{{ number_format($jobCard->labor_total, 2) }}</p></div>
        <div class="erp-card p-4"><p class="text-xs text-slate-500">{{ __('ui.total') }}</p><p class="text-xl font-bold text-orange-600">{{ number_format($jobCard->total_amount, 2) }}</p></div>
    </div>

    <div class="erp-card overflow-hidden">
        <table class="erp-table min-w-full">
            <thead class="bg-slate-50/80"><tr>
                <th>Type</th><th>{{ __('ui.description') }}</th><th>Qty</th><th>Unit Price</th><th class="text-right">Line Total</th>
            </tr></thead>
            <tbody>
                @foreach($jobCard->items as $item)
                    <tr>
                        <td>{{ ucfirst($item->item_type) }}</td>
                        <td>{{ $item->item_type === 'part' ? $item->part?->part_number : $item->description }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(!in_array($jobCard->status, ['invoiced', 'cancelled']))
        <div class="erp-card p-6">
            <h3 class="mb-4 text-base font-bold text-slate-900">Update Status</h3>
            <form method="POST" action="{{ route('job-cards.update-status', $jobCard) }}" class="flex flex-wrap gap-2">
                @csrf @method('PATCH')
                @if($jobCard->status === 'open')
                    <button name="status" value="in_progress" class="erp-btn-secondary">Start Work</button>
                @endif
                @if(in_array($jobCard->status, ['open', 'in_progress']))
                    <button name="status" value="completed" class="erp-btn-primary">Mark Completed</button>
                    <button name="status" value="cancelled" class="erp-btn-danger" onclick="return confirm('Cancel this job card?')">{{ __('ui.cancel') }}</button>
                @endif
            </form>
        </div>
    @endif

    @if($jobCard->status !== 'invoiced' && $jobCard->status !== 'cancelled')
        <div class="erp-card p-6">
            <h3 class="mb-4 text-base font-bold text-slate-900">{{ __('pages.actions.convert_to_sales_invoice') }}</h3>
            <form method="POST" action="{{ route('job-cards.convert', $jobCard) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <x-ui.form-field label="Invoice No" name="invoice_no" :value="$invoiceNo" required />
                <x-ui.form-field label="Invoice Date" name="invoice_date" type="date" :value="date('Y-m-d')" required />
                <x-ui.form-field label="Stock Location" name="default_location_id" type="select">
                    <option value="">— Use job card location —</option>
                    @foreach($locations as $l)<option value="{{ $l->id }}" @selected($jobCard->location_id == $l->id)>{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
                </x-ui.form-field>
                <x-ui.form-field label="Payment Type" name="invoice_type" type="select">
                    <option value="cash">Cash</option>
                    <option value="credit">Credit</option>
                </x-ui.form-field>
                <x-ui.form-field label="Status" name="status" type="select" hint="Posted deducts stock for parts">
                    <option value="draft">Draft</option>
                    <option value="posted">Posted</option>
                </x-ui.form-field>
                <div class="flex items-end md:col-span-2 lg:col-span-3">
                    <button type="submit" class="erp-btn-primary">{{ __('pages.actions.create_invoice') }}</button>
                </div>
            </form>
        </div>
    @elseif($jobCard->salesInvoice)
        <div class="erp-card p-4 text-sm">
            Linked invoice: <a href="{{ route('sales-invoices.index') }}" class="font-semibold text-orange-600">{{ $jobCard->salesInvoice->invoice_no }}</a>
        </div>
    @endif

    <a href="{{ route('job-cards.index') }}" class="erp-btn-secondary">{{ __('pages.actions.back_to_job_cards') }}</a>
</div>
</x-erp-layout>
