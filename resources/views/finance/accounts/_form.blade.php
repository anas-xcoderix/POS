<x-ui.form-field label="Account Code" name="account_code" required />
<x-ui.form-field label="Account Name" name="name" required />
<x-ui.form-field label="Name (AR)" name="name_ar" />
<x-ui.form-field label="Account Type" name="account_type" type="select" required>
    @foreach($accountTypes ?? ['asset','liability','equity','revenue','expense'] as $type)
        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field label="Parent Account" name="parent_id" type="select">
    <option value="">— None —</option>
    @foreach($parents as $p)
        <option value="{{ $p->id }}">{{ $p->account_code }} — {{ $p->name }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field label="Opening Balance" name="opening_balance" type="number" step="0.01" value="0" />
<x-ui.form-field label="Active" name="is_active" type="checkbox">Active account</x-ui.form-field>
