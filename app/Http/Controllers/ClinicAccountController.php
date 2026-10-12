<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClinicAccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Settings — Update Current User Profile
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $clinicId = Auth::guard('web')->user()->clinic_id;

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique('users', 'email')
                    ->ignore($userId, 'user_id'),
            ],

            'current_password' => [
                'required',
                'string',
                'current_password:web',
            ],
        ], [
            'current_password.required' =>
                'Enter your current password to save your profile.',

            'current_password.current_password' =>
                'Your current password is incorrect.',
        ]);

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

            DB::table('users')
                ->where('user_id', $userId)
                ->where('clinic_id', $clinicId)
                ->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);
        });

        $request->session()->regenerate();

        return redirect()
            ->route('settings.account')
            ->with('success', 'Your account profile has been updated.');
    }
}