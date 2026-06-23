@include('components.master-crud', [
    'title' => __('modules.employees'),
    'resource' => 'employees',
    'showRoute' => 'employees.show',
    'createLabel' => __('modules.add_employee'),
    'columns' => [
        ['label' => __('ui.employee_no'), 'field' => 'employee_no'],
        ['label' => __('ui.name'), 'field' => 'name'],
        ['label' => __('modules.departments'), 'relation' => 'department', 'field' => 'name'],
        ['label' => __('ui.job_title'), 'field' => 'job_title'],
        ['label' => __('ui.basic_salary'), 'field' => 'basic_salary', 'format' => 'money'],
        ['label' => __('ui.active'), 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('hr.employees._form')->render(),
])
