@php $title = __('transport.new_shipment'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('transport.shipments.store') }}" class="space-y-6">
    @csrf
    <div class="erp-card p-6">
        <h3 class="mb-4 font-bold text-slate-900">{{ __('transport.shipment_details') }}</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <x-ui.form-field :label="__('transport.shipment_no')" name="shipment_no" :value="old('shipment_no', $shipmentNo)" required />
            <x-ui.form-field :label="__('transport.ship_date')" name="ship_date" type="date" :value="old('ship_date', date('Y-m-d'))" required />
            <x-ui.form-field :label="__('transport.expected_date')" name="expected_date" type="date" :value="old('expected_date')" />
            <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" required>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id', $deliveryNote?->branch_id ?? $invoice?->branch_id ?? $defaultBranchId) == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('ui.customer')" name="customer_id" type="select" required>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id', $deliveryNote?->customer_id ?? $invoice?->customer_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('transport.driver')" name="transport_driver_id" type="select">
                <option value="">{{ __('forms.select') }}</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->vehicle_plate ?? $d->code }})</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('modules.delivery_note')" name="delivery_note_id" type="select">
                <option value="">{{ __('forms.select') }}</option>
                @foreach($deliveryNotes as $dn)
                    <option value="{{ $dn->id }}" @selected(old('delivery_note_id', $deliveryNote?->id) == $dn->id)>{{ $dn->dn_no }} — {{ $dn->customer?->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('modules.sales_invoices')" name="sales_invoice_id" type="select">
                <option value="">{{ __('forms.select') }}</option>
                @foreach($salesInvoices as $inv)
                    <option value="{{ $inv->id }}" @selected(old('sales_invoice_id', $invoice?->id) == $inv->id)>{{ $inv->invoice_no }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('ui.status')" name="status" type="select" required>
                @foreach(['pending','dispatched','in_transit'] as $s)
                    <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ __('transport.status.'.$s) }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('forms.plate_no')" name="vehicle_plate" :value="old('vehicle_plate')" />
            <x-ui.form-field :label="__('transport.ship_to')" name="ship_to_address" :value="old('ship_to_address', $deliveryNote?->customer?->address)" class="md:col-span-2" />
            <x-ui.form-field :label="__('transport.contact_phone')" name="contact_phone" :value="old('contact_phone', $deliveryNote?->customer?->phone)" />
            <x-ui.form-field :label="__('transport.transport_charge')" name="transport_charge" type="number" step="0.01" :value="old('transport_charge', 0)" />
            <x-ui.form-field :label="__('transport.cod_amount')" name="cod_amount" type="number" step="0.01" :value="old('cod_amount', 0)" />
            <x-ui.form-field :label="__('ui.remarks')" name="remarks" type="textarea" class="md:col-span-2 lg:col-span-3" />
        </div>
    </div>
    <div class="flex justify-end gap-3">
        <a href="{{ route('transport.shipments.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary">{{ __('ui.save') }}</button>
    </div>
</form>
</x-erp-layout>
