<x-ui.form-field :label="__('forms.code')" name="code" required />
<x-ui.form-field :label="__('forms.name')" name="name" required />
<x-ui.form-field :label="__('forms.phone')" name="phone" type="tel" />
<x-ui.form-field :label="__('forms.head_office')" name="is_head_office" type="checkbox">{{ __('forms.head_office') }}</x-ui.form-field>
<x-ui.form-field :label="__('forms.active')" name="is_active" type="checkbox">{{ __('forms.active') }}</x-ui.form-field>
