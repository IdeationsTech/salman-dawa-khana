<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Show Platform Admin Login
    |--------------------------------------------------------------------------
    */

    public function show(): View|RedirectResponse
    {
        if (Auth::guard('platform_admin')->check()) {
            return redirect()->route('platform-admin.dashboard');
        }

        return view('platform-admin.auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Authenticate Platform Admin
    |--------------------------------------------------------------------------
    */

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $throttleKey = 'platform-admin-login:' . hash(
            'sha256',
            $validated['email'] . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $authenticated = Auth::guard('platform_admin')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Unable to sign in with these credentials.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $admin = Auth::guard('platform_admin')->user();

        $admin->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->route('platform-admin.dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | Logout Platform Admin
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('platform_admin')->logout();

        $request->session()->regenerate(true);

        return redirect()->route('platform-admin.login');
    }
}