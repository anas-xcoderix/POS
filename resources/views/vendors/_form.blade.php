<x-ui.form-field :label="__('forms.code')" name="code" required />
<x-ui.form-field :label="__('forms.name')" name="name" required />
<x-ui.form-field :label="__('forms.phone')" name="phone" type="tel" />
<x-ui.form-field :label="__('forms.email')" name="email" type="email" />
<x-ui.form-field :label="__('forms.active')" name="is_active" type="checkbox">{{ __('forms.active') }}</x-ui.form-field>
