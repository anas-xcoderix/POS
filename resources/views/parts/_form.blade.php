<x-ui.form-field label="Part Number" name="part_number" required />
<x-ui.form-field label="OEM No" name="oem_no" />
<x-ui.form-field label="Barcode" name="barcode" />
<x-ui.form-field label="Brand" name="brand_id" type="select" required>
    @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Origin" name="origin_id" type="select">
    <option value="">— Select —</option>
    @foreach($origins as $origin)<option value="{{ $origin->id }}">{{ $origin->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Franchise" name="franchise_id" type="select">
    <option value="">— Select —</option>
    @foreach($franchises as $franchise)<option value="{{ $franchise->id }}">{{ $franchise->name }}</option>@endforeach
</x-ui.form-field>
<x-ui.form-field label="Description (EN)" name="description_en" required class="md:col-span-2" />
<x-ui.form-field label="Description (AR)" name="description_ar" class="md:col-span-2" />
<x-ui.form-field label="List Price" name="list_price" type="number" step="0.01" value="0" />
<x-ui.form-field label="Cost Price" name="cost_price" type="number" step="0.01" value="0" />
