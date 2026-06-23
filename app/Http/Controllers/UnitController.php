<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

class UnitController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Unit::class;
    }

    protected function viewPath(): string
    {
        return 'units';
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:20|unique:units,code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
        ];
    }
}
