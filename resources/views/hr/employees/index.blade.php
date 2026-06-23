@include('components.master-crud', [
    'title' => 'Employees',
    'resource' => 'employees',
    'createLabel' => 'Add Employee',
    'columns' => [
        ['label' => 'Employee No', 'field' => 'employee_no'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Department', 'relation' => 'department', 'field' => 'name'],
        ['label' => 'Job Title', 'field' => 'job_title'],
        ['label' => 'Basic Salary', 'field' => 'basic_salary', 'format' => 'money'],
        ['label' => 'Active', 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('hr.employees._form')->render(),
])
