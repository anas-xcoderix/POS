<x-ui.form-field label="Customer" name="customer_id" type="select">
    <option value="">— Walk-in / None —</option>
    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Plate No" name="plate_no" required />
<x-ui.form-field label="Make" name="make" />
<x-ui.form-field label="Model" name="model" />
<x-ui.form-field label="Year" name="year" />
<x-ui.form-field label="VIN" name="vin" />
<x-ui.form-field label="Color" name="color" />
<x-ui.form-field label="Istimara Expiry" name="istimara_expiry" type="date" />
<x-ui.form-field label="{{ __('ui.remarks') }}" name="remarks" type="textarea" />
<x-ui.form-field label="{{ __('ui.active') }}" name="is_active" type="checkbox" value="1">Vehicle is active</x-ui.form-field>
