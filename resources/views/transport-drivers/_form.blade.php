<div class="space-y-3">
    <x-ui.form-field :label="__('ui.code')" name="code" required />
    <x-ui.form-field :label="__('ui.name')" name="name" required />
    <x-ui.form-field :label="__('ui.name_ar')" name="name_ar" />
    <x-ui.form-field :label="__('transport.phone')" name="phone" />
    <x-ui.form-field :label="__('transport.license_no')" name="license_no" />
    <x-ui.form-field :label="__('forms.plate_no')" name="vehicle_plate" />
    <x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select">
        <option value="">{{ __('forms.select_branch') }}</option>
        @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
    </x-ui.form-field>
    <x-ui.form-field :label="__('ui.active')" name="is_active" type="checkbox">{{ __('ui.active') }}</x-ui.form-field>
</div>
