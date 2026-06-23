@include('components.master-crud', [
    'title' => 'Customers',
    'resource' => 'customers',
    'createLabel' => 'Add Customer',
    'columns' => [
        ['label' => 'Code', 'field' => 'code'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Type', 'field' => 'customer_type'],
        ['label' => 'Balance', 'field' => 'balance', 'format' => 'money'],
        ['label' => 'Credit Limit', 'field' => 'credit_limit', 'format' => 'money'],
    ],
    'formFields' => view('customers._form', ['branches' => $branches])->render(),
])
