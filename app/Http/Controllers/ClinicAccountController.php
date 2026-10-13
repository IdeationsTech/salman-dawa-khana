<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClinicAccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Account — Profile, Phone and Photo
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

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=3000,max_height=3000',
                'prohibited_if:remove_photo,1',
            ],

            'remove_photo' => [
                'nullable',
                'boolean',
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

            'profile_photo.prohibited_if' =>
                'Choose either a new photo or Remove current photo.',

            'profile_photo.max' =>
                'Your profile photo must not exceed 2 MB.',
        ]);

        $newPhotoPath = null;
        $oldPhotoPath = null;

        try {
            if ($request->hasFile('profile_photo')) {
                $newPhotoPath = $request
                    ->file('profile_photo')
                    ->store(
                        "clinics/{$clinicId}/users/{$userId}/photos",
                        'public'
                    );

                if (! $newPhotoPath) {
                    throw ValidationException::withMessages([
                        'profile_photo' =>
                            'Your profile photo could not be uploaded.',
                    ]);
                }
            }

            DB::transaction(function () use (
                $request,
                $validated,
                $userId,
                $clinicId,
                $newPhotoPath,
                &$oldPhotoPath
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

                $updates = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ];

                if (
                    $newPhotoPath !== null ||
                    $request->boolean('remove_photo')
                ) {
                    $oldPhotoPath = $user->profile_photo_path;
                    $updates['profile_photo_path'] = $newPhotoPath;
                }

                DB::table('users')
                    ->where('user_id', $userId)
                    ->where('clinic_id', $clinicId)
                    ->update($updates);
            });
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                try {
                    Storage::disk('public')->delete($newPhotoPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        if (
            $oldPhotoPath &&
            $oldPhotoPath !== $newPhotoPath &&
            Str::startsWith(
                $oldPhotoPath,
                "clinics/{$clinicId}/users/{$userId}/photos/"
            )
        ) {
            try {
                Storage::disk('public')->delete($oldPhotoPath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $request->session()->regenerate();

        return redirect()
            ->route('settings.account')
            ->with(
                'success',
                'Your account profile has been updated.'
            );
    }
}