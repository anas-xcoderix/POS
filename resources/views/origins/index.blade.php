@include('components.master-crud', [
    'title' => 'Origins',
    'resource' => 'origins',
    'createLabel' => 'Add Origin',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Arabic', 'field' => 'name_ar'],
    ],
    'formFields' => view('brands._form')->render(),
])
