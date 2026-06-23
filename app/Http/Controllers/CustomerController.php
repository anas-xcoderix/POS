<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Customer::class;
    }

    protected function viewPath(): string
    {
        return 'customers';
    }

    protected function withRelations(): array
    {
        return ['branch'];
    }

    protected function extraViewData(): array
    {
        return ['branches' => Branch::where('is_active', true)->orderBy('name')->get()];
    }

    protected function searchableColumns(): array
    {
        return ['code', 'name', 'phone', 'email'];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'code' => 'required|string|max:30|unique:customers,code,'.$id,
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'customer_type' => 'nullable|string|max:30',
            'price_level' => 'nullable|integer|in:1,2,3',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }
}
