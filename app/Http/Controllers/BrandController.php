<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;

class BrandController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Brand::class;
    }

    protected function viewPath(): string
    {
        return 'brands';
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:20|unique:brands,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
