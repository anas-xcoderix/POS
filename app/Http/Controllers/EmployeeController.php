<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class EmployeeController extends MasterDataController
{
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
        return ['branch', 'department'];
    }

    protected function extraViewData(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'employee_no' => 'required|string|max:30|unique:employees,employee_no,'.$id,
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'hire_date' => 'nullable|date',
            'job_title' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
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
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
