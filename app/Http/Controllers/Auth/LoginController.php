<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    /*
    |--------------------------------------------------------------------------
    | Clinic Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('web');
        $provider = $guard->getProvider();

        /*
        |--------------------------------------------------------------------------
        | Verify Credentials Before Checking Approval
        |--------------------------------------------------------------------------
        */

        $user = $provider->retrieveByCredentials($credentials);

        if (
            ! $user ||
            ! $provider->validateCredentials($user, $credentials)
        ) {
            return $this->invalidCredentials($request);
        }

        $clinic = DB::table('clinics')
            ->where('clinic_id', $user->clinic_id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Pending Clinic — Show Approval Screen
        |--------------------------------------------------------------------------
        */

        if (
            $clinic &&
            $clinic->onboarding_status === 'pending_review'
        ) {
            // Pending accounts are not signed into the application.
            $request->session()->forget('url.intended');

            return redirect()->route('registration.pending');
        }

        /*
        |--------------------------------------------------------------------------
        | Approved Clinic — Sign In
        |--------------------------------------------------------------------------
        */

        if (
            ! $user->is_active ||
            ! $clinic ||
            $clinic->onboarding_status !== 'approved'
        ) {
            return back()
                ->withErrors([
                    'email' =>
                        'Your account or clinic is not currently approved for access.',
                ])
                ->withInput(
                    $request->only('email', 'remember')
                );
        }

        if ($guard->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Clinic access middleware checks the current license.
            return redirect()->intended(
                route('dashboard')
            );
        }

        return $this->invalidCredentials($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Invalid Credentials
    |--------------------------------------------------------------------------
    */

    private function invalidCredentials(Request $request)
    {
        return back()
            ->withErrors([
                'email' =>
                    'These credentials do not match our records.',
            ])
            ->withInput(
                $request->only('email', 'remember')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}