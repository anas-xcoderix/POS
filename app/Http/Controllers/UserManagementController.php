<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\UserPermission;
use App\Services\GranularPermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private GranularPermissionService $granularPermissions) {}

    public function index(): View
    {
        $users = User::with('branch')->orderBy('name')->paginate(20);
        $permissionKeys = array_keys(config('erp.granular_permissions', []));

        $userPermissions = UserPermission::whereIn('user_id', $users->pluck('id'))
            ->where('granted', true)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($perms) => $perms->pluck('permission')->all());

        return view('users.index', [
            'records' => $users,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'roles' => array_keys(config('erp.roles', [])),
            'granularPermissions' => collect(config('erp.granular_permissions', []))
                ->mapWithKeys(fn ($label, $key) => [$key => __("permissions.{$key}")])
                ->all(),
            'userPermissions' => $userPermissions,
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

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $keys = array_keys(config('erp.granular_permissions', []));
        $permissions = [];

        foreach ($keys as $key) {
            if ($request->boolean("permissions.{$key}")) {
                $permissions[$key] = true;
            }
        }

        $this->granularPermissions->syncUserPermissions($user, $permissions);

        return back()->with('success', 'User permissions updated.');
    }
}
