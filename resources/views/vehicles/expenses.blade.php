@php $title = __('modules.vehicle_expenses').' — '.$vehicle->plate_no; @endphp
<x-erp-layout>
<div class="space-y-6">
    <div class="erp-card p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $vehicle->plate_no }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $vehicle->make }} {{ $vehicle->model }}
                    @if($vehicle->customer) · {{ $vehicle->customer->name }} @endif
                </p>
            </div>
            <a href="{{ route('vehicles.index') }}" class="erp-btn-secondary">Back to Vehicles</a>
        </div>
    </div>

    <div class="erp-card p-6">
        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-900">
            <x-ui.icon name="plus" class="h-5 w-5 text-orange-500" />
            Add Expense
        </h3>
        <form method="POST" action="{{ route('vehicles.expenses.store', $vehicle) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            @csrf
            <x-ui.form-field label="Date" name="expense_date" type="date" :value="old('expense_date', date('Y-m-d'))" required />
            <x-ui.form-field label="Type" name="expense_type" type="select" required>
                @foreach(['fuel', 'maintenance', 'insurance', 'registration', 'toll', 'other'] as $type)
                    <option value="{{ $type }}" @selected(old('expense_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Amount" name="amount" type="number" :value="old('amount')" required />
            <x-ui.form-field label="Reference No" name="reference_no" :value="old('reference_no')" />
            <x-ui.form-field label="Remarks" name="remarks" :value="old('remarks')" />
            <div class="flex items-end lg:col-span-5">
                <button type="submit" class="erp-btn-primary">Record Expense</button>
            </div>
        </form>
    </div>

    <div class="erp-card overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Expense History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table min-w-full">
                <thead class="bg-slate-50/80"><tr>
                    <th>Date</th><th>Type</th><th>Reference</th><th>Remarks</th><th class="text-right">Amount</th><th class="text-right">Action</th>
                </tr></thead>
                <tbody>
                    @php $total = 0; @endphp
                    @forelse($records as $row)
                        @php $total += (float) $row->amount; @endphp
                        <tr>
                            <td>{{ $row->expense_date?->format('M d, Y') }}</td>
                            <td><span class="erp-badge erp-badge-slate">{{ ucfirst($row->expense_type) }}</span></td>
                            <td>{{ $row->reference_no ?? '—' }}</td>
                            <td class="text-sm text-slate-600">{{ Str::limit($row->remarks, 40) ?? '—' }}</td>
                            <td class="text-right font-medium">{{ number_format($row->amount, 2) }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('vehicle-expenses.destroy', $row) }}" class="inline" onsubmit="return confirm('Remove this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="erp-btn-danger !p-2"><x-ui.icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <x-ui.empty-state title="No expenses" description="Record fuel, maintenance, and other vehicle costs above." />
                        </td></tr>
                    @endforelse
                </tbody>
                @if($records->isNotEmpty())
                    <tfoot class="bg-slate-50/50">
                        <tr>
                            <td colspan="4" class="text-right font-semibold">Total</td>
                            <td class="text-right text-lg font-bold text-slate-900">{{ number_format($total, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
</x-erp-layout>
