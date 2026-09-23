<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $loginCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (Auth::attempt($loginCredentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(
                route('dashboard')
            );
        }

        return back()
            ->withErrors([
                'email' => 'These credentials do not match our records.',
            ])
            ->withInput(
                $request->only('email', 'remember')
            );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}