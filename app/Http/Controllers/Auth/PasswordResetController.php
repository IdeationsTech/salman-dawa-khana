<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClinicAuditService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        Password::broker('users')->sendResetLink($data);

        return back()->with(
            'status',
            'If this email belongs to a clinic account, a password reset link has been generated.'
        );
    }

    public function form(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:160'],
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) > 72) {
                        $fail('The password must not exceed 72 bytes.');
                    }
                },
            ],
        ]);

        $status = DB::transaction(function () use ($data) {
            DB::table('password_reset_tokens')
                ->where('email', $data['email'])
                ->lockForUpdate()->first();

            return Password::broker('users')->reset(
                $data,
                function (User $user, string $password) {
                    $locked = User::whereKey($user->user_id)
                        ->lockForUpdate()->firstOrFail();

                    $locked->password = Hash::make($password);
                    $locked->setRememberToken(Str::random(60));
                    $locked->save();

                    app(ClinicAuditService::class)->record(
                        (int) $locked->clinic_id,
                        'user.password_reset',
                        'users',
                        (int) $locked->user_id,
                        ['password_changed' => true],
                        (int) $locked->user_id
                    );

                    event(new PasswordReset($locked));
                }
            );
        }, 3);

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => 'This reset link is invalid or expired. Request a new link.',
            ])->withInput($request->only('email'));
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'status',
            'Password reset successfully. Sign in with your new password.'
        );
    }
}