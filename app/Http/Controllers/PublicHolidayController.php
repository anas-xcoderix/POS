<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PublicHoliday;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class PublicHolidayController extends MasterDataController
{
    protected function modelClass(): string
    {
        return PublicHoliday::class;
    }

    protected function viewPath(): string
    {
        return 'hr.holidays';
    }

    protected function withRelations(): array
    {
        return ['branch'];
    }

    protected function extraViewData(): array
    {
        return [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function validationRules(?Model $record = null): array
    {
        $id = $record?->id;

        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'holiday_date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ];
    }

    public function index(\Illuminate\Http\Request $request): View
    {
        return view('hr.holidays.index', [
            'records' => PublicHoliday::with('branch')->orderByDesc('holiday_date')->paginate(20),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
