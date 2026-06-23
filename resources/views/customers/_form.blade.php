<x-ui.form-field label="Code" name="code" required />
<x-ui.form-field label="Branch" name="branch_id" type="select">
    <option value="">— Select branch —</option>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field label="Name" name="name" required />
<x-ui.form-field label="Phone" name="phone" type="tel" />
<x-ui.form-field label="Email" name="email" type="email" />
<x-ui.form-field label="Customer Type" name="customer_type" hint="e.g. retail, wholesale, corporate" />
<x-ui.form-field label="Active" name="is_active" type="checkbox">Active customer</x-ui.form-field>
