<x-ui.form-field :label="__('forms.employee_no')" name="employee_no" required />
<x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" required>
    @foreach($branches as $b)<option value="{{ $b->id }}">{{ localized($b) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.department')" name="department_id" type="select">
    <option value="">{{ __('ui.none') ?? '—' }}</option>
    @foreach($departments as $d)<option value="{{ $d->id }}">{{ localized($d) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('hr.linked_user')" name="user_id" type="select">
    <option value="">{{ __('forms.select') }}</option>
    @foreach($users ?? [] as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('ui.name')" name="name" required />
<x-ui.form-field :label="__('ui.name_ar')" name="name_ar" />
<x-ui.form-field :label="__('ui.phone')" name="phone" />
<x-ui.form-field :label="__('ui.email')" name="email" type="email" />
<x-ui.form-field :label="__('hr.start_date')" name="hire_date" type="date" />
<x-ui.form-field :label="__('forms.job_title')" name="job_title" />
<x-ui.form-field :label="__('forms.basic_salary')" name="basic_salary" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('hr.housing')" name="housing_allowance" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('hr.transport')" name="transport_allowance" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('hr.overtime_rate')" name="overtime_rate" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('hr.gosi_eligible')" name="gosi_eligible" type="checkbox" value="1">{{ __('hr.gosi_eligible') }}</x-ui.form-field>
<x-ui.form-field :label="__('hr.gosi_number')" name="gosi_number" />
<x-ui.form-field :label="__('hr.bank_name')" name="bank_name" />
<x-ui.form-field :label="__('hr.bank_account')" name="bank_account" />
<x-ui.form-field label="Aqama No" name="aqama_no" />
<x-ui.form-field label="Aqama Expiry" name="aqama_expiry" type="date" />
<x-ui.form-field label="License No" name="license_no" />
<x-ui.form-field label="License Expiry" name="license_expiry" type="date" />
<x-ui.form-field :label="__('ui.active')" name="is_active" type="checkbox" value="1">{{ __('ui.active') }}</x-ui.form-field>
