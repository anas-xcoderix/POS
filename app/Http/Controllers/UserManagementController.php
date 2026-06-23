<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'records' => User::with('branch')->orderBy('name')->paginate(20),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'roles' => array_keys(config('erp.roles', [])),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => 'required|string|in:'.implode(',', array_keys(config('erp.roles', []))),
            'branch_id' => 'nullable|exists:branches,id',
            'max_discount_percent' => 'required|numeric|min:0|max:100',
            'can_access_all_branches' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'You cannot remove your own admin role.');
        }

        $user->update([
            'role' => $data['role'],
            'branch_id' => $data['branch_id'] ?? null,
            'max_discount_percent' => $data['max_discount_percent'],
            'can_access_all_branches' => $request->boolean('can_access_all_branches'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'User updated.');
    }
}
