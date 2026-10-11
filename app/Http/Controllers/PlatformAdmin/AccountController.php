<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('platform-admin.account.index', [
            'admin' => Auth::guard('platform_admin')->user(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Account — Update Profile
    |--------------------------------------------------------------------------
    */

    public function updateProfile(Request $request): RedirectResponse
    {
        $adminId = Auth::guard('platform_admin')->id();

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],

            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique('platform_admins', 'email')
                    ->ignore($adminId, 'platform_admin_id'),
            ],

            'profile_current_password' => [
                'required',
                'string',
                'current_password:platform_admin',
            ],
        ], [
            'profile_current_password.required' =>
                'Enter your current password to update your profile.',

            'profile_current_password.current_password' =>
                'Your current password is incorrect.',
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $adminId
        ) {
            $admin = DB::table('platform_admins')
                ->where('platform_admin_id', $adminId)
                ->lockForUpdate()
                ->first();

            abort_unless($admin && $admin->is_active, 403);

            if (!Hash::check(
                $validated['profile_current_password'],
                $admin->password
            )) {
                throw ValidationException::withMessages([
                    'profile_current_password' =>
                        'Your current password is incorrect.',
                ]);
            }

            $oldValues = [
                'name' => $admin->name,
                'email' => $admin->email,
            ];

            $newValues = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if ($oldValues === $newValues) {
                return;
            }

            $updates = $newValues;
            $updates['updated_at'] = now();

            if ($admin->email !== $validated['email']) {
                $updates['email_verified_at'] = null;
            }

            DB::table('platform_admins')
                ->where('platform_admin_id', $adminId)
                ->update($updates);

            $this->recordAudit(
                $request,
                $adminId,
                'platform_admin.profile_updated',
                $oldValues,
                $newValues
            );
        });

        return redirect()
            ->route('platform-admin.account.index')
            ->with('success', 'Your profile has been updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Account — Change Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request): RedirectResponse
    {
        $adminId = Auth::guard('platform_admin')->id();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password:platform_admin',
            ],

            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) > 72) {
                        $fail(
                            'The new password must not exceed 72 bytes.'
                        );
                    }
                },
            ],
        ], [
            'current_password.current_password' =>
                'Your current password is incorrect.',

            'password.min' =>
                'Use at least 12 characters for your new password.',

            'password.confirmed' =>
                'The new password confirmation does not match.',
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $adminId
        ) {
            $admin = DB::table('platform_admins')
                ->where('platform_admin_id', $adminId)
                ->lockForUpdate()
                ->first();

            abort_unless($admin && $admin->is_active, 403);

            if (!Hash::check(
                $validated['current_password'],
                $admin->password
            )) {
                throw ValidationException::withMessages([
                    'current_password' =>
                        'Your current password is incorrect.',
                ]);
            }

            if (Hash::check(
                $validated['password'],
                $admin->password
            )) {
                throw ValidationException::withMessages([
                    'password' =>
                        'Choose a password different from your current password.',
                ]);
            }

            DB::table('platform_admins')
                ->where('platform_admin_id', $adminId)
                ->update([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                    'updated_at' => now(),
                ]);

            $this->recordAudit(
                $request,
                $adminId,
                'platform_admin.password_changed',
                null,
                ['password_changed' => true]
            );
        });

        $request->session()->regenerate();

        return redirect()
            ->route('platform-admin.account.index')
            ->with('success', 'Your password has been changed.');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Account — Audit Records
    |--------------------------------------------------------------------------
    */

    private function recordAudit(
        Request $request,
        int $adminId,
        string $action,
        ?array $oldValues,
        array $newValues
    ): void {
        DB::table('platform_audit_logs')->insert([
            'platform_admin_id' => $adminId,
            'clinic_id' => null,
            'action' => $action,
            'entity_type' => 'platform_admin',
            'entity_id' => $adminId,

            'old_values' => $oldValues === null
                ? null
                : json_encode($oldValues, JSON_THROW_ON_ERROR),

            'new_values' => json_encode(
                $newValues,
                JSON_THROW_ON_ERROR
            ),

            'ip_address' => $request->ip(),
            'user_agent' => Str::limit(
                (string) $request->userAgent(),
                500,
                ''
            ),
            'created_at' => now(),
        ]);
    }
}