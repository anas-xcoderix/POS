<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Cache;

class GranularPermissionService
{
    public function __construct(private PermissionService $permissions) {}

    public function can(User $user, string $permission): bool
    {
        if ($this->permissions->can($user, '*') || $user->role === 'admin') {
            return true;
        }

        $override = Cache::remember(
            "user_perm:{$user->id}:{$permission}",
            300,
            fn () => UserPermission::where('user_id', $user->id)->where('permission', $permission)->first()
        );

        if ($override) {
            return $override->granted;
        }

        return $this->permissions->can($user, $this->moduleFromPermission($permission));
    }

    public function assert(User $user, string $permission): void
    {
        if (! $this->can($user, $permission)) {
            throw new \RuntimeException('Permission denied: '.$permission);
        }
    }

    public function syncUserPermissions(User $user, array $permissions): void
    {
        UserPermission::where('user_id', $user->id)->delete();

        foreach ($permissions as $key => $granted) {
            if ($granted) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $key,
                    'granted' => true,
                ]);
            }
        }

        Cache::flush();
    }

    public function allDefinitions(): array
    {
        return config('erp.granular_permissions', []);
    }

    protected function moduleFromPermission(string $permission): string
    {
        return explode('.', $permission)[0] ?? $permission;
    }
}
