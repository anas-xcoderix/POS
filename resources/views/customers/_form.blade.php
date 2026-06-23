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
<x-ui.form-field label="Customer Type" name="customer_type" type="select">
    <option value="retail">Retail</option>
    <option value="wholesale">Wholesale</option>
    <option value="corporate">Corporate</option>
</x-ui.form-field>
<x-ui.form-field label="Price Level" name="price_level" type="select">
    <option value="1">List Price</option>
    <option value="2">Price 2</option>
    <option value="3">Price 3</option>
</x-ui.form-field>
<x-ui.form-field label="Customer Discount %" name="discount_percent" type="number" step="0.01" value="0" />
<x-ui.form-field label="Credit Limit" name="credit_limit" type="number" step="0.01" value="0" hint="0 = unlimited" />
<x-ui.form-field label="Payment Terms (days)" name="payment_terms_days" type="number" value="0" />
<x-ui.form-field label="Active" name="is_active" type="checkbox">Active customer</x-ui.form-field>
