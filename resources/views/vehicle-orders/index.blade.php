@php
    $title = 'Vehicle Orders';
    $branches = $branches ?? \App\Models\Branch::where('is_active', true)->get();
    $customers = $customers ?? \App\Models\Customer::where('is_active', true)->get();
    $orderNo = 'VO-'.now()->format('Ymd').'-'.str_pad((string) (\App\Models\VehicleOrder::count() + 1), 4, '0', STR_PAD_LEFT);
@endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <x-ui.icon name="plus" class="h-5 w-5 text-orange-500" />
            New Vehicle Order
        </h3>
        <form method="POST" action="{{ route('vehicle-orders.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            @csrf
            <x-ui.form-field label="Order No" name="order_no" :value="old('order_no', $orderNo)" required />
            <x-ui.form-field label="Order Date" name="order_date" type="date" :value="old('order_date', date('Y-m-d'))" required />
            <x-ui.form-field label="{{ __('ui.branch') }}" name="branch_id" type="select" required>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Customer" name="customer_id" type="select" required>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Vehicle Make" name="vehicle_make" :value="old('vehicle_make')" />
            <x-ui.form-field label="Vehicle Model" name="vehicle_model" :value="old('vehicle_model')" />
            <x-ui.form-field label="Estimated Amount" name="estimated_amount" type="number" :value="old('estimated_amount', 0)" />
            <x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" :value="old('remarks')" />
            <div class="flex items-end lg:col-span-4">
                <button type="submit" class="erp-btn-primary">Create Order</button>
            </div>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Vehicle Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Order No</th><th>{{ __('ui.customer') }}</th><th>Branch</th><th>{{ __('ui.date') }}</th><th>Vehicle</th><th class="text-right">Est. Amount</th><th>{{ __('ui.status') }}</th><th class="text-right">{{ __('ui.actions') }}</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $row)
                        @php
                            $statusClass = match($row->status) {
                                'completed' => 'erp-badge-green',
                                'cancelled' => 'erp-badge-slate',
                                'in_progress' => 'erp-badge-orange',
                                default => 'erp-badge-amber',
                            };
                        @endphp
                        <tr>
                            <td class="font-semibold">{{ $row->order_no }}</td>
                            <td>{{ $row->customer?->name }}</td>
                            <td>{{ $row->branch?->code }}</td>
                            <td>{{ $row->order_date?->format('M d, Y') }}</td>
                            <td>
                                @if($row->vehicle_make || $row->vehicle_model)
                                    {{ trim($row->vehicle_make.' '.$row->vehicle_model) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right font-medium">{{ number_format($row->estimated_amount, 2) }}</td>
                            <td><span class="erp-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('vehicle-orders.update', $row) }}" class="inline-flex items-center gap-1">
                                        @csrf @method('PUT')
                                        <select name="status" class="erp-input !mt-0 !py-1.5 text-xs w-32" onchange="this.form.submit()">
                                            @foreach(['open', 'in_progress', 'completed', 'cancelled'] as $s)
                                                <option value="{{ $s }}" @selected($row->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <form method="POST" action="{{ route('vehicle-orders.destroy', $row) }}" class="inline" onsubmit="return confirm('Delete this order?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">
                            <x-ui.empty-state title="No vehicle orders" description="Create a vehicle order using the form above." />
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $records->links() }}</div>
    </div>
</div>
</x-erp-layout>
