@include('components.master-crud', [
    'title' => __('modules.franchises'),
    'resource' => 'franchises',
    'createLabel' => __('modules.add_franchise'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.arabic_name'), 'field' => 'name_ar'],
    ],
    'formFields' => view('brands._form')->render(),
])
