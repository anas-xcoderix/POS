@include('components.master-crud', [
    'title' => __('modules.branches'),
    'resource' => 'branches',
    'createLabel' => __('modules.add_branch'),
    'columns' => [
        ['label' => __('ui.code'), 'field' => 'code'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('ui.phone'), 'field' => 'phone'],
        ['label' => __('ui.head_office'), 'field' => 'is_head_office', 'type' => 'boolean'],
    ],
    'formFields' => view('branches._form')->render(),
])
