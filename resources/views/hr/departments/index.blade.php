@include('components.master-crud', [
    'title' => 'Departments',
    'resource' => 'departments',
    'createLabel' => 'Add Department',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Active', 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('hr.departments._form')->render(),
])
