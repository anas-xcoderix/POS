@include('components.master-crud', [
    'title' => __('modules.customers'),
    'resource' => 'customers',
    'createLabel' => __('modules.add_customer'),
    'printUrl' => route('documents.masters.customers.pdf'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.type'), 'field' => 'customer_type'],
        ['label' => __('ui.balance'), 'field' => 'balance', 'format' => 'money'],
        ['label' => __('pdf.credit_limit'), 'field' => 'credit_limit', 'format' => 'money'],
    ],
    'formFields' => view('customers._form', ['branches' => $branches])->render(),
])
