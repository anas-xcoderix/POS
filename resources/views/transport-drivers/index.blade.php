@include('components.master-crud', [
    'title' => __('transport.drivers'),
    'resource' => 'transport-drivers',
    'createLabel' => __('transport.add_driver'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('transport.phone'), 'field' => 'phone'],
        ['label' => __('forms.plate_no'), 'field' => 'vehicle_plate'],
        ['label' => __('ui.branch'), 'relation' => 'branch', 'field' => 'name'],
    ],
    'formFields' => view('transport-drivers._form', ['branches' => $branches])->render(),
])
