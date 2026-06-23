@php $title = 'Register Fixed Asset'; @endphp
<x-erp-layout>
<form method="POST" action="{{ route('fixed-assets.store') }}" class="erp-card max-w-2xl p-6 space-y-4">
    @csrf
    <x-ui.form-field label="Asset Code" name="asset_code" :value="$assetCode" required />
    <x-ui.form-field label="Name" name="name" required />
    <x-ui.form-field label="Arabic Name" name="name_ar" />
    <x-ui.form-field label="Category" name="category_id" type="select" required>
        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="Branch" name="branch_id" type="select" required>
        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="Location" name="location_id" type="select">
        <option value="">— None —</option>
        @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field label="Purchase Date" name="purchase_date" type="date" :value="date('Y-m-d')" required />
    <x-ui.form-field label="Purchase Value" name="purchase_value" type="number" step="0.01" required />
    <x-ui.form-field label="Salvage Value" name="salvage_value" type="number" step="0.01" value="0" />
    <x-ui.form-field label="Useful Life (months)" name="useful_life_months" type="number" />
    <x-ui.form-field label="Remarks" name="remarks" type="textarea" />
    <div class="flex gap-3 justify-end">
        <a href="{{ route('fixed-assets.index') }}" class="erp-btn-secondary">Cancel</a>
        <button class="erp-btn-primary">Register</button>
    </div>
</form>
</x-erp-layout>
