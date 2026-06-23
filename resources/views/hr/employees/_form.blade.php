<x-ui.form-field label="Employee No" name="employee_no" required />
<x-ui.form-field label="Branch" name="branch_id" type="select" required>
    @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Department" name="department_id" type="select">
    <option value="">— None —</option>
    @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Name" name="name" required />
<x-ui.form-field label="Name (Arabic)" name="name_ar" />
<x-ui.form-field label="Phone" name="phone" />
<x-ui.form-field label="Email" name="email" type="email" />
<x-ui.form-field label="Hire Date" name="hire_date" type="date" />
<x-ui.form-field label="Job Title" name="job_title" />
<x-ui.form-field label="Basic Salary" name="basic_salary" type="number" step="0.01" value="0" />
<x-ui.form-field label="Housing Allowance" name="housing_allowance" type="number" step="0.01" value="0" />
<x-ui.form-field label="Transport Allowance" name="transport_allowance" type="number" step="0.01" value="0" />
<x-ui.form-field label="Aqama No" name="aqama_no" />
<x-ui.form-field label="Aqama Expiry" name="aqama_expiry" type="date" />
<x-ui.form-field label="License No" name="license_no" />
<x-ui.form-field label="License Expiry" name="license_expiry" type="date" />
<x-ui.form-field label="Active" name="is_active" type="checkbox" value="1">Employee is active</x-ui.form-field>
