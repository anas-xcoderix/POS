@include('components.master-crud', [
    'title' => 'Vehicles',
    'resource' => 'vehicles',
    'createLabel' => 'Add Vehicle',
    'columns' => [
        ['label' => 'Plate No', 'field' => 'plate_no'],
        ['label' => 'Customer', 'relation' => 'customer', 'field' => 'name'],
        ['label' => 'Make / Model', 'field' => 'make'],
        ['label' => 'Istimara Expiry', 'field' => 'istimara_expiry'],
        ['label' => 'Active', 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('workshop.vehicles._form')->render(),
])
