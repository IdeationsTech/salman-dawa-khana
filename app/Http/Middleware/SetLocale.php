<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $guard = $request->is('platform-admin*') ? 'platform_admin' : 'web';
        $locale = Auth::guard($guard)->user()?->preferred_locale ?? $request->session()->get('locale', 'en');
        app()->setLocale(in_array($locale, ['en', 'ur'], true) ? $locale : 'en');
        return $next($request);
    }
}
