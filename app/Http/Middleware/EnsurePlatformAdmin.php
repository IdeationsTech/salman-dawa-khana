<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('platform_admin');

        $admin = $guard->user();

        /*
        |--------------------------------------------------------------------------
        | Require Platform Admin Login
        |--------------------------------------------------------------------------
        */

        if (! $admin) {
            return redirect()->route('platform-admin.login');
        }

        /*
        |--------------------------------------------------------------------------
        | Block Inactive Platform Admins
        |--------------------------------------------------------------------------
        */

        if (! $admin->is_active) {
            $guard->logout();

            $request->session()->regenerate(true);

            return redirect()
                ->route('platform-admin.login')
                ->withErrors([
                    'email' => 'Your administrator account is inactive.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Use Platform Admin Authentication For This Request
        |--------------------------------------------------------------------------
        */

        Auth::shouldUse('platform_admin');

        return $next($request);
    }
}