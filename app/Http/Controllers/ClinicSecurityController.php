<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClinicSecurityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Security — Change Current User Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
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
                'Your new password must contain at least 8 characters.',

            'password.confirmed' =>
                'The new password confirmation does not match.',
        ]);

        $userId = (int) $request->user()->user_id;
        $clinicId = (int) $request->user()->clinic_id;

        DB::transaction(function () use (
            $validated,
            $userId,
            $clinicId
        ) {
            $user = DB::table('users')
                ->where('user_id', $userId)
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->first();

            abort_unless($user && $user->is_active, 403);

            if (! Hash::check(
                $validated['current_password'],
                $user->password
            )) {
                throw ValidationException::withMessages([
                    'current_password' =>
                        'Your current password is incorrect.',
                ]);
            }

            if (Hash::check(
                $validated['password'],
                $user->password
            )) {
                throw ValidationException::withMessages([
                    'password' =>
                        'Choose a password different from your current password.',
                ]);
            }

            DB::table('users')
                ->where('user_id', $userId)
                ->where('clinic_id', $clinicId)
                ->update([
                    'password' => Hash::make($validated['password']),
                ]);
        });

        $request->session()->regenerate();

        return redirect()
            ->route('settings.security')
            ->with('success', 'Your password has been changed.');
    }
}