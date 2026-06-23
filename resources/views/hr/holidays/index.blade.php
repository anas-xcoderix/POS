@include('components.master-crud', [
    'title' => __('hr.public_holidays'),
    'resource' => 'public-holidays',
    'createLabel' => __('hr.add_holiday'),
    'columns' => [
        ['label' => __('hr.holiday_name'), 'field' => 'name'],
        ['label' => __('hr.holiday_date'), 'field' => 'holiday_date', 'format' => 'date'],
        ['label' => __('ui.branch'), 'relation' => 'branch', 'field' => 'name'],
        ['label' => __('ui.active'), 'field' => 'is_active', 'type' => 'boolean'],
    ],
    'formFields' => view('hr.holidays._form')->render(),
    'records' => $records,
])
