@include('components.master-crud', [
    'title' => __('modules.locations'),
    'resource' => 'locations',
    'createLabel' => __('modules.add_location'),
    'columns' => [
        ['label' => __('ui.branch'), 'relation' => 'branch', 'field' => 'name'],
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.location_type'), 'field' => 'location_type'],
        ['label' => __('ui.bin'), 'field' => 'bin'],
    ],
    'formFields' => view('locations._form', ['branches' => $branches])->render(),
])
