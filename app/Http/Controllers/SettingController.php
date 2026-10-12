<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Settings — Clinic Profile
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view(
            'settings.index',
            compact('clinic', 'settings')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settings — User Account
    |--------------------------------------------------------------------------
    */

    public function account()
    {
        $clinic = $this->clinic();
        $user = Auth::user();

        return view(
            'settings.account',
            compact('clinic', 'user')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settings — Payment Preferences
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view(
            'settings.payments',
            compact('clinic', 'settings')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settings — Notifications
    |--------------------------------------------------------------------------
    */

    public function notifications()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view(
            'settings.notifications',
            compact('clinic', 'settings')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settings — Security
    |--------------------------------------------------------------------------
    */

    public function security()
    {
        $clinic = $this->clinic();
        $user = Auth::user();

        return view(
            'settings.security',
            compact('clinic', 'user')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settings — Save Clinic Details, Logo and Preferences
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => [
                'sometimes',
                'required',
                'string',
                'max:160',
            ],

            'clinic_phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
            ],

            'clinic_address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'clinic_logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=3000,max_height=3000',
                'prohibited_if:remove_logo,1',
            ],

            'remove_logo' => [
                'nullable',
                'boolean',
            ],

            'currency' => [
                'sometimes',
                'required',
                Rule::in(['AED', 'PKR', 'USD']),
            ],

            'settings' => [
                'nullable',
                'array',
            ],

            'settings.*' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'clinic_logo.prohibited_if' =>
                'Choose either a new logo or Remove current logo.',

            'clinic_logo.max' =>
                'The clinic logo must not exceed 2 MB.',

            'clinic_logo.dimensions' =>
                'The clinic logo must not exceed 3000 × 3000 pixels.',
        ]);

        $clinicId = Auth::user()->clinic_id;
        $newLogoPath = null;
        $oldLogoPath = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | Store the New Logo
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('clinic_logo')) {
                $newLogoPath = $request
                    ->file('clinic_logo')
                    ->store(
                        "clinics/{$clinicId}/logos",
                        'public'
                    );

                if (! $newLogoPath) {
                    throw ValidationException::withMessages([
                        'clinic_logo' =>
                            'The clinic logo could not be uploaded.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update Only the Signed-In User's Clinic
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $request,
                $validated,
                $clinicId,
                $newLogoPath,
                &$oldLogoPath
            ) {
                $clinic = DB::table('clinics')
                    ->where('clinic_id', $clinicId)
                    ->lockForUpdate()
                    ->first();

                abort_unless($clinic, 404);

                $clinicData = [];

                foreach ([
                    'clinic_name' => 'name',
                    'clinic_phone' => 'phone',
                    'clinic_address' => 'address',
                    'currency' => 'currency',
                ] as $input => $column) {
                    if (array_key_exists($input, $validated)) {
                        $clinicData[$column] = $validated[$input];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Replace or Remove the Current Logo
                |--------------------------------------------------------------------------
                */

                if (
                    $newLogoPath !== null ||
                    $request->boolean('remove_logo')
                ) {
                    $oldLogoPath = $clinic->logo_path;

                    // A removal without a new upload stores NULL.
                    $clinicData['logo_path'] = $newLogoPath;
                }

                if ($clinicData !== []) {
                    $clinicData['updated_at'] = now();

                    DB::table('clinics')
                        ->where('clinic_id', $clinicId)
                        ->update($clinicData);
                }

                /*
                |--------------------------------------------------------------------------
                | Save Clinic Preferences
                |--------------------------------------------------------------------------
                */

                foreach (
                    ($validated['settings'] ?? [])
                    as $key => $value
                ) {
                    Setting::updateOrCreate(
                        [
                            'clinic_id' => $clinicId,
                            'setting_key' => $key,
                        ],
                        [
                            'setting_value' => $value,
                        ]
                    );
                }
            });
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Remove the New File If Database Saving Fails
            |--------------------------------------------------------------------------
            */

            if ($newLogoPath) {
                try {
                    Storage::disk('public')->delete($newLogoPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Delete the Previous Logo After a Successful Save
        |--------------------------------------------------------------------------
        */

        if (
            $oldLogoPath &&
            $oldLogoPath !== $newLogoPath &&
            Str::startsWith(
                $oldLogoPath,
                "clinics/{$clinicId}/logos/"
            )
        ) {
            try {
                Storage::disk('public')->delete($oldLogoPath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve the Current Clinic
    |--------------------------------------------------------------------------
    */

    private function clinic()
    {
        return Clinic::where(
            'clinic_id',
            Auth::user()->clinic_id
        )->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve the Current Clinic's Preferences
    |--------------------------------------------------------------------------
    */

    private function settings()
    {
        return Setting::where(
            'clinic_id',
            Auth::user()->clinic_id
        )
            ->get()
            ->keyBy('setting_key');
    }
}