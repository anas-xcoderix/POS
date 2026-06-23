@include('components.master-crud', [
    'title' => 'Brands',
    'resource' => 'brands',
    'createLabel' => 'Add Brand',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Arabic', 'field' => 'name_ar'],
        ['label' => 'Status', 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('brands._form')->render(),
])
