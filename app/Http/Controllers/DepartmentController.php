<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

class DepartmentController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Department::class;
    }

    protected function viewPath(): string
    {
        return 'hr.departments';
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:20|unique:departments,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
