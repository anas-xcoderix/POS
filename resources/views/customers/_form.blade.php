<x-ui.form-field :label="__('forms.code')" name="code" required />
<x-ui.form-field :label="__('forms.branch')" name="branch_id" type="select">
    <option value="">{{ __('forms.select_branch') }}</option>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ localized($branch) }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.name')" name="name" required />
<x-ui.form-field :label="__('forms.phone')" name="phone" type="tel" />
<x-ui.form-field :label="__('forms.email')" name="email" type="email" />
<x-ui.form-field :label="__('forms.customer_type')" name="customer_type" type="select">
    <option value="retail">{{ __('forms.type_retail') }}</option>
    <option value="wholesale">{{ __('forms.type_wholesale') }}</option>
    <option value="corporate">{{ __('forms.type_corporate') }}</option>
</x-ui.form-field>
<x-ui.form-field :label="__('forms.price_level')" name="price_level" type="select">
    <option value="1">{{ __('forms.price_list') }}</option>
    <option value="2">{{ __('forms.price_2') }}</option>
    <option value="3">{{ __('forms.price_3') }}</option>
</x-ui.form-field>
<x-ui.form-field :label="__('forms.discount_percent')" name="discount_percent" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.credit_limit')" name="credit_limit" type="number" step="0.01" value="0" :hint="__('forms.credit_unlimited')" />
<x-ui.form-field :label="__('forms.payment_terms')" name="payment_terms_days" type="number" value="0" />
<x-ui.form-field :label="__('forms.active')" name="is_active" type="checkbox">{{ __('forms.active_customer') }}</x-ui.form-field>
