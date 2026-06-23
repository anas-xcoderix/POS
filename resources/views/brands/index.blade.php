@include('components.master-crud', [
    'title' => __('modules.brands'),
    'resource' => 'brands',
    'createLabel' => __('modules.add_brand'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.arabic_name'), 'field' => 'name_ar'],
        ['label' => __('ui.status'), 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('brands._form')->render(),
])
