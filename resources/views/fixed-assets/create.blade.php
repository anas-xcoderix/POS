@php $title = __('modules.register_fixed_asset'); @endphp
<x-erp-layout>
<form method="POST" action="{{ route('fixed-assets.store') }}" class="erp-card max-w-2xl p-6 space-y-4">
    @csrf
    <x-ui.form-field :label="__('forms.asset_code')" name="asset_code" :value="$assetCode" required />
    <x-ui.form-field :label="__('ui.name')" name="name" required />
    <x-ui.form-field :label="__('ui.name_ar')" name="name_ar" />
    <x-ui.form-field :label="__('forms.category')" name="category_id" type="select" required>
        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" required>
        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field :label="__('ui.location')" name="location_id" type="select">
        <option value="">{{ __('pages.users.none') }}</option>
        @foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->branch?->code }} / {{ $l->code }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field :label="__('forms.purchase_date')" name="purchase_date" type="date" :value="date('Y-m-d')" required />
    <x-ui.form-field :label="__('forms.purchase_value')" name="purchase_value" type="number" step="0.01" required />
    <x-ui.form-field :label="__('forms.salvage_value')" name="salvage_value" type="number" step="0.01" value="0" />
    <x-ui.form-field :label="__('forms.useful_life')" name="useful_life_months" type="number" />
    <x-ui.form-field :label="__('ui.remarks')" name="remarks" type="textarea" />
    <div class="flex gap-3 justify-end">
        <a href="{{ route('fixed-assets.index') }}" class="erp-btn-secondary">{{ __('ui.cancel') }}</a>
        <button class="erp-btn-primary">{{ __('pages.actions.register') }}</button>
    </div>
</form>
</x-erp-layout>
