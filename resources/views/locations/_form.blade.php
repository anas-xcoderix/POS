<x-ui.form-field label="Branch" name="branch_id" type="select" required>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</x-ui.form-field>
<x-ui.form-field label="Code" name="code" required />
<x-ui.form-field label="Name" name="name" required />
<x-ui.form-field label="Aisle" name="aisle" />
<x-ui.form-field label="Rack" name="rack" />
<x-ui.form-field label="Bin" name="bin" />
<x-ui.form-field label="Active" name="is_active" type="checkbox">Active location</x-ui.form-field>
