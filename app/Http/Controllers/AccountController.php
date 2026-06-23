<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class AccountController extends MasterDataController
{
    protected function modelClass(): string
    {
        return Account::class;
    }

    protected function viewPath(): string
    {
        return 'finance.accounts';
    }

    protected function searchableColumns(): array
    {
        return ['account_code', 'name'];
    }

    protected function extraViewData(): array
    {
        return [
            'parents' => Account::orderBy('account_code')->get(),
            'accountTypes' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
        ];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'account_code' => 'required|string|max:30|unique:accounts,account_code,'.$id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ];
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        $query = Account::query()->with('parent');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('account_type')) {
            $query->where('account_type', $type);
        }

        return view('finance.accounts.index', [
            'records' => $query->orderBy('account_code')->paginate(20)->withQueryString(),
            'search' => $search,
            'accountType' => $type,
            'parents' => Account::orderBy('account_code')->get(),
            'accountTypes' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
        ]);
    }
}
