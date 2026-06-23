<x-ui.form-field :label="__('forms.part_number')" name="part_number" required />
<x-ui.form-field :label="__('forms.oem_no')" name="oem_no" />
<x-ui.form-field :label="__('forms.barcode')" name="barcode" />
<x-ui.form-field :label="__('forms.brand')" name="brand_id" type="select" required>
    @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ localized($brand) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.origin')" name="origin_id" type="select">
    <option value="">{{ __('forms.select') }}</option>
    @foreach($origins as $origin)<option value="{{ $origin->id }}">{{ localized($origin) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.franchise')" name="franchise_id" type="select">
    <option value="">{{ __('forms.select') }}</option>
    @foreach($franchises as $franchise)<option value="{{ $franchise->id }}">{{ localized($franchise) }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.description_en')" name="description_en" required class="md:col-span-2" />
<x-ui.form-field :label="__('forms.description_ar')" name="description_ar" class="md:col-span-2" />
<x-ui.form-field :label="__('forms.list_price')" name="list_price" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.price_2')" name="price_2" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.price_3')" name="price_3" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.cost_price')" name="cost_price" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.track_batch')" name="track_batch" type="checkbox">{{ __('forms.track_batch') }}</x-ui.form-field>
<x-ui.form-field :label="__('forms.track_serial')" name="track_serial" type="checkbox">{{ __('forms.track_serial') }}</x-ui.form-field>
