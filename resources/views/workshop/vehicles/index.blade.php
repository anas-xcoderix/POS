@include('components.master-crud', [
    'title' => __('modules.vehicles'),
    'resource' => 'vehicles',
    'createLabel' => __('modules.add_vehicle'),
    'columns' => [
        ['label' => __('ui.plate_no'), 'field' => 'plate_no'],
        ['label' => __('ui.customer'), 'relation' => 'customer', 'field' => 'name'],
        ['label' => __('ui.make_model'), 'field' => 'make'],
        ['label' => __('ui.istimara_expiry'), 'field' => 'istimara_expiry'],
        ['label' => __('ui.active'), 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('workshop.vehicles._form')->render(),
])
