@include('components.master-crud', [
    'title' => __('modules.vendors'),
    'resource' => 'vendors',
    'createLabel' => __('modules.add_vendor'),
    'printUrl' => route('documents.masters.vendors.pdf'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.phone'), 'field' => 'phone'],
    ],
    'formFields' => view('vendors._form')->render(),
])
