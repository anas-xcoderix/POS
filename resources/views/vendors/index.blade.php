@include('components.master-crud', [
    'title' => 'Vendors',
    'resource' => 'vendors',
    'createLabel' => 'Add Vendor',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Phone', 'field' => 'phone'],
    ],
    'formFields' => view('vendors._form')->render(),
])
