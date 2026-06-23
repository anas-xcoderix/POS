<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureErpPermission
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (! $user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        $routeName = $request->route()?->getName();
        if ($routeName && str_starts_with($routeName, 'profile.')) {
            return $next($request);
        }

        if ($routeName && ! $this->permissions->canRoute($user, $routeName)) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
