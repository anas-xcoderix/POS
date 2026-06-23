@include('components.master-crud', [
    'title' => 'Franchises',
    'resource' => 'franchises',
    'createLabel' => 'Add Franchise',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Arabic', 'field' => 'name_ar'],
    ],
    'formFields' => view('brands._form')->render(),
])
