<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Setting;
use App\Rules\InternationalPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettingController extends Controller
{
    public function index()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view('settings.index', compact('clinic', 'settings'));
    }

    public function account()
    {
        $clinic = $this->clinic();
        $user = Auth::user();

        return view('settings.account', compact('clinic', 'user'));
    }

    public function payments()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view('settings.payments', compact('clinic', 'settings'));
    }

    public function notifications()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view('settings.notifications', compact('clinic', 'settings'));
    }

    public function security()
    {
        $clinic = $this->clinic();
        $user = Auth::user();

        return view('settings.security', compact('clinic', 'user'));
    }

    public function update(Request $request)
    {
        abort_unless(
            $request->user()->role?->name === 'Owner',
            403,
            'Only the clinic owner can change clinic settings.'
        );

        $validated = $request->validate([
            'clinic_name' => ['sometimes', 'required', 'string', 'max:160'],

            'clinic_phone' => [
                'bail',
                'sometimes',
                'required',
                'string',
                'max:16',
                new InternationalPhone,
            ],

            'clinic_address' => ['nullable', 'string', 'max:255'],

            'clinic_logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=3000,max_height=3000',
                'prohibited_if:remove_logo,1',
            ],

            'remove_logo' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array:print_location,timezone'],
            'settings.timezone' => ['sometimes', 'required', 'timezone'],
            'settings.print_location' => ['nullable', 'string', 'max:160'],

            'currency' => ['prohibited'],
            'country_code' => ['prohibited'],
            'patient_id_country' => ['prohibited'],
            'decimal_places' => ['prohibited'],
            'cash_enabled' => ['prohibited'],
            'bank_enabled' => ['prohibited'],
            'card_enabled' => ['prohibited'],
            'show_previous_due' => ['prohibited'],
            'allow_partial_payments' => ['prohibited'],
        ], [
            'clinic_logo.prohibited_if' => 'Choose either a new logo or Remove current logo.',
            'clinic_logo.max' => 'The clinic logo must not exceed 2 MB.',
            'clinic_logo.dimensions' => 'The clinic logo must not exceed 3000 × 3000 pixels.',
            'settings.array' => 'Only print location and timezone can be changed through Clinic Profile.',
            'currency.prohibited' => 'Only the platform administrator can change clinic currency.',
            'country_code.prohibited' => 'Only the platform administrator can change clinic country.',
            'patient_id_country.prohibited' => 'Use the Patient identification form to change this preference.',
        ]);

        $clinicId = (int) $request->user()->clinic_id;
        $newLogoPath = null;
        $oldLogoPath = null;

        try {
            if ($request->hasFile('clinic_logo')) {
                $newLogoPath = $request->file('clinic_logo')
                    ->store("clinics/{$clinicId}/logos", 'public');

                if (! $newLogoPath) {
                    throw ValidationException::withMessages([
                        'clinic_logo' => 'The clinic logo could not be uploaded.',
                    ]);
                }
            }

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
                ] as $input => $column) {
                    if (array_key_exists($input, $validated)) {
                        $clinicData[$column] = $validated[$input];
                    }
                }

                if ($newLogoPath !== null || $request->boolean('remove_logo')) {
                    $oldLogoPath = $clinic->logo_path;
                    $clinicData['logo_path'] = $newLogoPath;
                }

                if ($clinicData !== []) {
                    DB::table('clinics')
                        ->where('clinic_id', $clinicId)
                        ->update([...$clinicData, 'updated_at' => now()]);
                }

                $preferences = $validated['settings'] ?? [];
                if (isset($preferences['timezone'])) {
                    Setting::updateOrCreate(['clinic_id' => $clinicId, 'setting_key' => 'timezone'], ['setting_value' => $preferences['timezone']]);
                    request()->attributes->remove('clinic.timezone.'.$clinicId);
                }

                if (array_key_exists('print_location', $preferences)) {
                    $location = trim((string) ($preferences['print_location'] ?? ''));

                    Setting::updateOrCreate([
                        'clinic_id' => $clinicId,
                        'setting_key' => 'print_location',
                    ], [
                        'setting_value' => $location !== '' ? $location : null,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                try {
                    Storage::disk('public')->delete($newLogoPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        if (
            $oldLogoPath
            && $oldLogoPath !== $newLogoPath
            && Str::startsWith($oldLogoPath, "clinics/{$clinicId}/logos/")
        ) {
            try {
                Storage::disk('public')->delete($oldLogoPath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    private function clinic()
    {
        return Clinic::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->firstOrFail();
    }

    private function settings()
    {
        return Setting::query()
            ->where('clinic_id', Auth::user()->clinic_id)
            ->get()
            ->keyBy('setting_key');
    }
}
