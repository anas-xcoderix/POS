@php $title = __('modules.stock_adjustment'); @endphp
<x-erp-layout>
<div class="max-w-2xl">
    <div class="erp-card p-6">
        <p class="mb-6 text-sm text-slate-600">Set the exact on-hand quantity for a part at a specific location. The system records the difference as an adjustment movement.</p>
        <form method="POST" action="{{ route('stock.adjustment.store') }}" class="space-y-4">
            @csrf
            <x-ui.form-field label="Branch" name="branch_id" type="select" required>
                @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Location" name="location_id" type="select" required>
                @foreach($locations as $l)
                    <option value="{{ $l->id }}">{{ $l->branch?->name }} / {{ $l->code }} — {{ $l->name }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="Part" name="part_id" type="select" required>
                @foreach($parts as $p)
                    <option value="{{ $p->id }}">{{ $p->part_number }} — {{ Str::limit($p->description_en, 40) }}</option>
                @endforeach
            </x-ui.form-field>
            <x-ui.form-field label="New Quantity (on hand)" name="new_quantity" type="number" step="0.01" min="0" value="0" required />
            <x-ui.form-field label="Remarks" name="remarks" hint="Reason for adjustment" />
            <div class="flex gap-3 pt-4">
                <a href="{{ route('stock.movements') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>
</x-erp-layout>
