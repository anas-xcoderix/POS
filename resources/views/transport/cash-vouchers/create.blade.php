@php $title = __('transport.new_voucher'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('transport.cash-vouchers.store') }}" class="space-y-6">
    @csrf
    <div class="erp-card p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-ui.form-field :label="__('transport.voucher_no')" name="voucher_no" :value="old('voucher_no', $voucherNo)" required />
            <x-ui.form-field :label="__('ui.date')" name="voucher_date" type="date" :value="old('voucher_date', date('Y-m-d'))" required />
            <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" required>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id', $defaultBranchId) == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('transport.driver')" name="transport_driver_id" type="select" required id="driverSelect">
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected(old('transport_driver_id', $driverId) == $d->id)>{{ $d->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field :label="__('ui.remarks')" name="remarks" class="md:col-span-2 lg:col-span-4" />
        </div>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold">{{ __('transport.cod_collections') }}</h3>
            <p class="text-sm text-slate-500">{{ __('transport.cod_collections_hint') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>{{ __('transport.shipment_no') }}</th>
                    <th>{{ __('ui.customer') }}</th>
                    <th>{{ __('transport.cod_outstanding') }}</th>
                    <th>{{ __('transport.collect_amount') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($shipments as $i => $s)
                        <tr>
                            <td class="font-medium">{{ $s->shipment_no }}</td>
                            <td>{{ $s->customer?->name }}</td>
                            <td>{{ number_format($s->codOutstanding(), 2) }}</td>
                            <td>
                                <input type="hidden" name="lines[{{ $i }}][shipment_id]" value="{{ $s->id }}">
                                <input type="number" step="0.01" name="lines[{{ $i }}][amount]" value="{{ old('lines.'.$i.'.amount', $s->codOutstanding()) }}" class="erp-input !mt-0 w-32" min="0" max="{{ $s->codOutstanding() }}">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-ui.empty-state :title="__('transport.empty.cod_shipments')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('transport.cash-vouchers.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary" @disabled($shipments->isEmpty())>{{ __('ui.save') }}</button>
    </div>
</form>
</x-erp-layout>
