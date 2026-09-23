<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view(
            'settings.index',
            compact('clinic', 'settings')
        );
    }

    public function account()
    {
        $clinic = $this->clinic();
        $user = Auth::user();

        return view(
            'settings.account',
            compact('clinic', 'user')
        );
    }

    public function payments()
    {
        $clinic = $this->clinic();
        $settings = $this->settings();

        return view(
            'settings.payments',
            compact('clinic', 'settings')
        );
    }

    public function notifications()
    {
        $settings = $this->settings();

        return view(
            'settings.notifications',
            compact('settings')
        );
    }

    public function security()
    {
        $user = Auth::user();

        return view(
            'settings.security',
            compact('user')
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => [
                'nullable',
                'string',
                'max:160',
            ],

            'clinic_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'clinic_address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'currency' => [
                'nullable',
                'string',
                'size:3',
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
        ]);

        $clinicId = Auth::user()->clinic_id;

        DB::transaction(function () use (
            $validated,
            $clinicId
        ) {
            $clinicData = [];

            if (array_key_exists('clinic_name', $validated)) {
                $clinicData['name'] =
                    $validated['clinic_name'];
            }

            if (array_key_exists('clinic_phone', $validated)) {
                $clinicData['phone'] =
                    $validated['clinic_phone'];
            }

            if (array_key_exists('clinic_address', $validated)) {
                $clinicData['address'] =
                    $validated['clinic_address'];
            }

            if (array_key_exists('currency', $validated)) {
                $clinicData['currency'] =
                    strtoupper($validated['currency']);
            }

            if (! empty($clinicData)) {
                Clinic::where(
                    'clinic_id',
                    $clinicId
                )->update($clinicData);
            }

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

        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }

    private function clinic()
    {
        return Clinic::where(
            'clinic_id',
            Auth::user()->clinic_id
        )->firstOrFail();
    }

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