@include('components.master-crud', [
    'title' => __('modules.departments'),
    'resource' => 'departments',
    'createLabel' => __('modules.add_department'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.active'), 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('hr.departments._form')->render(),
])
