@include('components.master-crud', [
    'title' => 'Fixed Asset Categories',
    'resource' => 'fixed-asset-categories',
    'createLabel' => 'Add Category',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Life (months)', 'field' => 'default_life_months'],
        ['label' => 'Status', 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('fixed-asset-categories._form')->render(),
])
