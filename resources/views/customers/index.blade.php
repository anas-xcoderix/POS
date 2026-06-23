@include('components.master-crud', [
    'title' => 'Customers',
    'resource' => 'customers',
    'createLabel' => 'Add Customer',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Phone', 'field' => 'phone'],
        ['label' => 'Type', 'field' => 'customer_type'],
    ],
    'formFields' => view('customers._form', ['branches' => $branches])->render(),
])
