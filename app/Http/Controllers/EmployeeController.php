<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HrService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends MasterDataController
{
    public function __construct(private HrService $hr) {}

    protected function modelClass(): string
    {
        return Employee::class;
    }

    protected function viewPath(): string
    {
        return 'hr.employees';
    }

    protected function searchableColumns(): array
    {
        return ['employee_no', 'name', 'phone', 'email', 'job_title'];
    }

    protected function withRelations(): array
    {
        return ['branch', 'department', 'user'];
    }

    protected function extraViewData(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'departments' => \App\Models\Department::where('is_active', true)->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    protected function validationRules(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        $id = $record?->id;

        return [
            'employee_no' => 'required|string|max:30|unique:employees,employee_no,'.$id,
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'hire_date' => 'nullable|date',
            'job_title' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'gosi_eligible' => 'boolean',
            'gosi_number' => 'nullable|string|max:30',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'overtime_rate' => 'nullable|numeric|min:0',
            'aqama_no' => 'nullable|string|max:30',
            'aqama_expiry' => 'nullable|date',
            'license_no' => 'nullable|string|max:30',
            'license_expiry' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        $query = Employee::query()->with(['branch', 'department']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_no', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        return view('hr.employees.index', [
            'records' => $query->orderBy('employee_no')->paginate(15)->withQueryString(),
            'search' => $search,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'departments' => \App\Models\Department::where('is_active', true)->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function show(Employee $employee): View
    {
        $employee->load(['branch', 'department', 'user', 'payrollItems.payrollRun']);

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $attendance = $this->hr->employeeAttendanceSummary($employee, $month, $year);
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $leaveBalances = $leaveTypes->mapWithKeys(fn ($t) => [
            $t->id => $this->hr->leaveBalance($employee, $t->id, $year),
        ]);

        return view('hr.employees.show', [
            'employee' => $employee,
            'attendance' => $attendance,
            'leaveBalances' => $leaveBalances,
            'leaveTypes' => $leaveTypes,
            'month' => $month,
            'year' => $year,
            'payrollHistory' => $employee->payrollItems()->with('payrollRun')->latest()->limit(12)->get(),
        ]);
    }
}
