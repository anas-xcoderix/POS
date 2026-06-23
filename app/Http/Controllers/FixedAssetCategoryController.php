<?php

namespace App\Http\Controllers;

use App\Models\FixedAssetCategory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetCategoryController extends MasterDataController
{
    protected function modelClass(): string
    {
        return FixedAssetCategory::class;
    }

    protected function viewPath(): string
    {
        return 'fixed-asset-categories';
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:20|unique:fixed_asset_categories,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'default_life_months' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ];
    }
}
