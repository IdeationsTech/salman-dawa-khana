<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicRole
{
    /*
    |--------------------------------------------------------------------------
    | Clinic Access — Allowed Roles
    |--------------------------------------------------------------------------
    */

    public function handle(
        Request $request,
        Closure $next,
        string ...$allowedRoles
    ): Response {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403);

        $roleName = $user->role?->name;

        abort_unless(
            in_array($roleName, $allowedRoles, true),
            403,
            'Your role does not have permission to access this section.'
        );

        return $next($request);
    }
}