@php $title = $vehicle->stock_no; @endphp
<x-erp-layout>
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 erp-card p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-slate-500">{{ __('forms.chassis_no') }}</dt><dd class="font-semibold">{{ $vehicle->chassis_no }}</dd></div>
            <div><dt class="text-slate-500">{{ __('forms.model') }}</dt><dd>{{ localized($vehicle->model) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('forms.color') }}</dt><dd>{{ localized($vehicle->color) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('ui.branch') }}</dt><dd>{{ localized($vehicle->branch) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('ui.status') }}</dt><dd>{{ __('forms.status_'.$vehicle->status) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('forms.list_price') }}</dt><dd>{{ number_format($vehicle->list_price, 2) }}</dd></div>
        </dl>
        @if($vehicle->transfers->isNotEmpty())
            <h3 class="mt-6 mb-3 font-bold">{{ __('modules.showroom_transfers') }}</h3>
            <table class="erp-table min-w-full text-sm">
                <thead><tr><th>{{ __('forms.transfer_no') }}</th><th>{{ __('forms.from') }}</th><th>{{ __('forms.to') }}</th><th>{{ __('ui.status') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach($vehicle->transfers as $t)
                        <tr>
                            <td>{{ $t->transfer_no }}</td>
                            <td>{{ $t->fromBranch?->name }}</td>
                            <td>{{ $t->toBranch?->name }}</td>
                            <td>{{ $t->status }}</td>
                            <td>
                                @if($t->status === 'pending')
                                    <form method="POST" action="{{ route('showroom-transfers.receive', $t) }}">@csrf
                                        <button class="erp-btn-secondary text-xs">{{ __('forms.receive') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="space-y-4">
        @if($vehicle->status === 'in_stock')
            <div class="erp-card p-5">
                <h3 class="mb-3 font-bold">{{ __('forms.transfer_vehicle') }}</h3>
                <form method="POST" action="{{ route('showroom-vehicles.transfer', $vehicle) }}" class="space-y-3">@csrf
                    <x-ui.form-field :label="__('forms.to_branch')" name="to_branch_id" type="select" required>
                        @foreach($branches as $b)
                            @if($b->id != $vehicle->branch_id)<option value="{{ $b->id }}">{{ $b->name }}</option>@endif
                        @endforeach
                    </x-ui.form-field>
                    <button class="erp-btn-primary w-full">{{ __('forms.transfer') }}</button>
                </form>
            </div>
            <div class="erp-card p-5">
                <h3 class="mb-3 font-bold">{{ __('forms.sell_vehicle') }}</h3>
                <form method="POST" action="{{ route('showroom-vehicles.sell', $vehicle) }}" class="space-y-3">@csrf
                    <x-ui.form-field :label="__('ui.customer')" name="customer_id" type="select" required>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ localized($c) }}</option>@endforeach
                    </x-ui.form-field>
                    <button class="erp-btn-primary w-full">{{ __('forms.mark_sold') }}</button>
                </form>
            </div>
        @endif
    </div>
</div>
</x-erp-layout>
