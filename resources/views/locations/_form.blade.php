<x-ui.form-field label="Branch" name="branch_id" type="select" required>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field label="Code" name="code" required />
<x-ui.form-field label="Name" name="name" required />
<x-ui.form-field label="{{ __('ui.location_type') }}" name="location_type" type="select" required>
    <option value="warehouse">{{ __('ui.location_warehouse') }}</option>
    <option value="showroom">{{ __('ui.location_showroom') }}</option>
    <option value="workshop">{{ __('ui.location_workshop') }}</option>
</x-ui.form-field>
<x-ui.form-field label="Aisle" name="aisle" />
<x-ui.form-field label="Rack" name="rack" />
<x-ui.form-field label="Bin" name="bin" />
<x-ui.form-field label="Active" name="is_active" type="checkbox">{{ __('ui.active') }}</x-ui.form-field>
