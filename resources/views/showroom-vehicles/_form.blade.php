<x-ui.form-field :label="__('forms.stock_no')" name="stock_no" :value="$stockNo ?? old('stock_no')" required />
<x-ui.form-field :label="__('ui.branch')" name="branch_id" type="select" required>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}" @selected(old('branch_id', $defaultBranchId ?? null) == $branch->id)>{{ localized($branch) }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.model')" name="model_id" type="select" required>
    @foreach($models as $model)
        <option value="{{ $model->id }}">{{ localized($model) }} ({{ $model->code }})</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.color')" name="color_id" type="select">
    <option value="">—</option>
    @foreach($colors as $color)
        <option value="{{ $color->id }}">{{ localized($color) }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.franchise')" name="franchise_id" type="select">
    <option value="">—</option>
    @foreach($franchises as $franchise)
        <option value="{{ $franchise->id }}">{{ localized($franchise) }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field :label="__('forms.chassis_no')" name="chassis_no" required />
<x-ui.form-field :label="__('forms.engine_no')" name="engine_no" />
<x-ui.form-field :label="__('forms.year')" name="year" type="number" />
<x-ui.form-field :label="__('forms.purchase_cost')" name="purchase_cost" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.list_price')" name="list_price" type="number" step="0.01" value="0" />
<x-ui.form-field :label="__('forms.received_date')" name="received_date" type="date" :value="old('received_date', now()->toDateString())" required class="md:col-span-2" />
<x-ui.form-field :label="__('ui.remarks')" name="remarks" type="textarea" class="md:col-span-2" />
