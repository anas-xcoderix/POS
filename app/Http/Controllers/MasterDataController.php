<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class MasterDataController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function viewPath(): string;

    abstract protected function validationRules(?Model $record = null): array;

    protected function withRelations(): array
    {
        return [];
    }

    protected function extraViewData(): array
    {
        return [];
    }

    public function index(Request $request): View
    {
        $query = $this->modelClass()::query()->with($this->withRelations());

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableColumns() as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return view($this->viewPath().'.index', [
            'records' => $query->latest()->paginate(15)->withQueryString(),
            'search' => $search,
            ...$this->extraViewData(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->validationRules());
        $data = $this->normalizeBooleans($data);

        $this->modelClass()::create($data);

        return back()->with('success', 'Record created successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->modelClass()::findOrFail($id);
        $data = $request->validate($this->validationRules($record));
        $data = $this->normalizeBooleans($data);
        $record->update($data);

        return back()->with('success', 'Record updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->modelClass()::findOrFail($id)->delete();

        return back()->with('success', 'Record deleted successfully.');
    }

    protected function searchableColumns(): array
    {
        return ['name', 'code'];
    }

    protected function normalizeBooleans(array $data): array
    {
        foreach (['is_active', 'is_head_office', 'is_kit', 'is_returnable'] as $field) {
            if (array_key_exists($field, $data) || request()->has($field)) {
                $data[$field] = request()->boolean($field);
            }
        }

        return $data;
    }
}
