<x-ui.form-field :label="__('hr.holiday_name')" name="name" required />
<x-ui.form-field :label="__('ui.name_ar')" name="name_ar" />
<x-ui.form-field :label="__('hr.holiday_date')" name="holiday_date" type="date" required />
<x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select">
    <option value="">{{ __('hr.all_branches') }}</option>
    @foreach($branches as $b)<option value="{{ $b->id }}">{{ localized($b) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('ui.active')" name="is_active" type="checkbox" value="1">{{ __('ui.active') }}</x-ui.form-field>
