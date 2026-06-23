@php $title = 'DN '.$note->dn_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $note->dn_no }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $note->customer?->name }} · {{ $note->branch?->name }}</p>
                <p class="text-sm text-slate-500">
                    Delivery Date: {{ $note->delivery_date?->format('M d, Y') }}
                    @if($note->salesInvoice)
                        · Invoice: {{ $note->salesInvoice->invoice_no }}
                    @endif
                </p>
                @if($note->driver_name || $note->vehicle_plate)
                    <p class="mt-1 text-sm text-slate-500">
                        @if($note->driver_name)Driver: {{ $note->driver_name }}@endif
                        @if($note->vehicle_plate) · Plate: {{ $note->vehicle_plate }}@endif
                    </p>
                @endif
                @if($note->remarks)
                    <p class="mt-2 text-sm"><strong>Remarks:</strong> {{ $note->remarks }}</p>
                @endif
            </div>
            <span class="erp-badge {{ $note->status === 'delivered' ? 'erp-badge-green' : 'erp-badge-amber' }}">
                {{ ucfirst($note->status) }}
            </span>
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Delivered Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Part</th><th>{{ __('ui.description') }}</th><th class="text-right">Quantity</th>
                </tr></thead>
                <tbody>
                    @forelse($note->items as $item)
                        <tr>
                            <td class="font-medium">{{ $item->part?->part_number }}</td>
                            <td class="text-sm text-slate-600">{{ Str::limit($item->part?->description_en, 60) }}</td>
                            <td class="text-right font-medium">{{ number_format($item->quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">
                            <x-ui.empty-state title="{{ __('pages.empty.delivery_items') }}" description="{{ __('pages.empty.delivery_items_hint') }}" />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('documents.delivery-note.pdf', $note) }}" class="erp-btn-secondary">{{ __('ui.pdf') }}</a>
        <form method="POST" action="{{ route('transport.shipments.from-delivery-note', $note) }}">
            @csrf
            <button class="erp-btn-primary">{{ __('transport.new_shipment') }}</button>
        </form>
        <a href="{{ route('delivery-notes.index') }}" class="erp-btn-secondary">{{ __('pages.actions.back_to_delivery_notes') }}</a>
    </div>
</div>
</x-erp-layout>
