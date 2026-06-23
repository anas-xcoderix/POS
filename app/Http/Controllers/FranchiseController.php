<?php

namespace App\Http\Controllers;

use App\Models\Franchise;
use Illuminate\Database\Eloquent\Model;

class FranchiseController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Franchise::class;
    }

    protected function viewPath(): string
    {
        return 'franchises';
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:20|unique:franchises,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
