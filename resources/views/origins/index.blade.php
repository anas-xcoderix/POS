@include('components.master-crud', [
    'title' => __('modules.origins'),
    'resource' => 'origins',
    'createLabel' => __('modules.add_origin'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.arabic_name'), 'field' => 'name_ar'],
    ],
    'formFields' => view('brands._form')->render(),
])
