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

        $userPermissions = UserPermission::whereIn('user_id', $users->pluck('id'))
            ->where('granted', true)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($perms) => $perms->pluck('permission')->all());

        return view('users.index', [
            'records' => $users,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'roles' => array_keys(config('erp.roles', [])),
            'granularPermissions' => $this->translatedPermissions(),
            'userPermissions' => $userPermissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:'.implode(',', array_keys(config('erp.roles', []))),
            'branch_id' => 'nullable|exists:branches,id',
            'max_discount_percent' => 'required|numeric|min:0|max:100',
            'can_access_all_branches' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'branch_id' => $data['branch_id'] ?? null,
            'max_discount_percent' => $data['max_discount_percent'],
            'can_access_all_branches' => $request->boolean('can_access_all_branches'),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $this->granularPermissions->syncUserPermissions($user, $this->parsePermissionInput($request));

        return back()->with('success', __('messages.user.created'));
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
            return back()->with('error', __('messages.user.cannot_remove_own_admin'));
        }

        $user->update([
            'role' => $data['role'],
            'branch_id' => $data['branch_id'] ?? null,
            'max_discount_percent' => $data['max_discount_percent'],
            'can_access_all_branches' => $request->boolean('can_access_all_branches'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.user.updated'));
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $this->granularPermissions->syncUserPermissions($user, $this->parsePermissionInput($request));

        return back()->with('success', __('messages.user.permissions_updated'));
    }

    /** @return array<string, string> */
    protected function translatedPermissions(): array
    {
        return collect(config('erp.granular_permissions', []))
            ->mapWithKeys(fn ($label, $key) => [$key => __("permissions.{$key}")])
            ->all();
    }

    /** @return array<string, bool> */
    protected function parsePermissionInput(Request $request): array
    {
        $permissions = [];

        foreach (array_keys(config('erp.granular_permissions', [])) as $key) {
            if ($request->boolean("permissions.{$key}")) {
                $permissions[$key] = true;
            }
        }

        return $permissions;
    }
}
