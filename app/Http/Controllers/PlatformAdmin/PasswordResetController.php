<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Notifications\PlatformAdminResetPassword;
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
        return view('platform-admin.auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        Password::broker('platform_admins')->sendResetLink(
            [
                'email' => $validated['email'],
                'is_active' => true,
            ],
            function (PlatformAdmin $admin, string $token) {
                $admin->notify(
                    new PlatformAdminResetPassword($token)
                );
            }
        );

        return back()->with(
            'status',
            'If this email belongs to an active platform admin account, '
            . 'a password reset link has been requested.'
        );
    }

    public function form(Request $request, string $token)
    {
        return view('platform-admin.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:160'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) > 72) {
                        $fail(
                            'The password must not exceed 72 bytes.'
                        );
                    }
                },
            ],
        ]);

        $status = DB::transaction(function () use (
            $request,
            $validated
        ) {
            /*
             * Serialize attempts using the same reset-token record.
             * The password broker checks the token and deletes it
             * after a successful reset.
             */
            DB::table('platform_admin_password_reset_tokens')
                ->where('email', $validated['email'])
                ->lockForUpdate()
                ->first();

            return Password::broker('platform_admins')->reset(
                [
                    'email' => $validated['email'],
                    'token' => $validated['token'],
                    'password' => $validated['password'],
                    'password_confirmation' =>
                        $request->input('password_confirmation'),
                    'is_active' => true,
                ],
                function (
                    PlatformAdmin $admin,
                    string $password
                ) use ($request) {
                    $locked = PlatformAdmin::whereKey(
                        $admin->getKey()
                    )->where('is_active', true)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $locked->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    /*
                     * Record the event without storing the
                     * password, hash or reset token.
                     */
                    DB::table('platform_audit_logs')->insert([
                        'platform_admin_id' => $locked->getKey(),
                        'clinic_id' => null,
                        'action' => 'platform_admin.password_reset',
                        'entity_type' => 'platform_admins',
                        'entity_id' => $locked->getKey(),
                        'old_values' => null,
                        'new_values' => json_encode([
                            'password_changed' => true,
                        ], JSON_THROW_ON_ERROR),
                        'ip_address' => $request->ip(),
                        'user_agent' => Str::limit(
                            (string) $request->userAgent(),
                            500,
                            ''
                        ),
                        'created_at' => now(),
                    ]);

                    event(new PasswordReset($locked));
                }
            );
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors([
                    'email' => 'This reset link is invalid or expired. '
                        . 'Request a new link.',
                ])
                ->withInput($request->only('email'));
        }

        Auth::guard('platform_admin')->logout();
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect()
            ->route('platform-admin.login')
            ->with(
                'status',
                'Password reset successfully. Sign in with your new password.'
            );
    }
}