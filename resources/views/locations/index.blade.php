@include('components.master-crud', [
    'title' => 'Locations',
    'resource' => 'locations',
    'createLabel' => 'Add Location',
    'columns' => [
        ['label' => 'Branch', 'relation' => 'branch', 'field' => 'name'],
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Bin', 'field' => 'bin'],
    ],
    'formFields' => view('locations._form', ['branches' => $branches])->render(),
])
