@php $title = $shipment->shipment_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $shipment->shipment_no }}</h2>
                <p class="text-sm text-slate-500">{{ $shipment->customer?->name }} · {{ $shipment->branch?->name }}</p>
            </div>
            <span class="erp-badge erp-badge-slate">{{ __('transport.status.'.$shipment->status) }}</span>
        </div>
        <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div><dt class="text-slate-500">{{ __('transport.ship_date') }}</dt><dd class="font-medium">{{ $shipment->ship_date?->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('transport.driver') }}</dt><dd class="font-medium">{{ $shipment->driver?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('transport.transport_charge') }}</dt><dd class="font-medium">{{ number_format($shipment->transport_charge, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('transport.cod_amount') }}</dt><dd class="font-medium">{{ number_format($shipment->cod_amount, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('transport.cod_collected') }}</dt><dd class="font-medium">{{ number_format($shipment->cod_collected, 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('transport.cod_outstanding') }}</dt><dd class="font-medium text-orange-600">{{ number_format($shipment->codOutstanding(), 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('modules.delivery_note') }}</dt><dd class="font-medium">{{ $shipment->deliveryNote?->dn_no ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('modules.sales_invoices') }}</dt><dd class="font-medium">{{ $shipment->salesInvoice?->invoice_no ?? '—' }}</dd></div>
            @if($shipment->ship_to_address)
                <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('transport.ship_to') }}</dt><dd class="font-medium">{{ $shipment->ship_to_address }}</dd></div>
            @endif
        </dl>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 font-bold">{{ __('transport.update_status') }}</h3>
        <form method="POST" action="{{ route('transport.shipments.update-status', $shipment) }}" class="flex flex-wrap items-end gap-3">
            @csrf @method('PATCH')
            <x-ui.form-field :label="__('ui.status')" name="status" type="select" class="!mb-0 sm:w-48">
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($shipment->status === $s)>{{ __('transport.status.'.$s) }}</option>
                @endforeach
            </x-ui.form-field>
            <button class="erp-btn-primary">{{ __('ui.update') }}</button>
        </form>
    </div>

    @if($shipment->codOutstanding() > 0 && $shipment->driver_id)
        <div class="erp-card p-6">
            <a href="{{ route('transport.cash-vouchers.create', ['driver_id' => $shipment->transport_driver_id]) }}" class="erp-btn-secondary">
                {{ __('transport.create_cash_voucher') }}
            </a>
        </div>
    @endif

    <a href="{{ route('transport.shipments.index') }}" class="erp-btn-secondary">{{ __('transport.back_to_shipments') }}</a>
</div>
</x-erp-layout>
