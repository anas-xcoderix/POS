<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

class VendorController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Vendor::class;
    }

    protected function viewPath(): string
    {
        return 'vendors';
    }

    protected function searchableColumns(): array
    {
        return ['code', 'name', 'phone', 'email'];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:30|unique:vendors,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
