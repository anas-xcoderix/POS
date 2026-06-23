@include('components.master-crud', [
    'title' => 'Branches',
    'resource' => 'branches',
    'createLabel' => 'Add Branch',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Phone', 'field' => 'phone'],
        ['label' => 'Head Office', 'field' => 'is_head_office', 'type' => 'boolean'],
    ],
    'formFields' => view('branches._form')->render(),
])
