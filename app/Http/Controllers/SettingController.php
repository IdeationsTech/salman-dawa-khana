<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = $this->clinicSettings();

        return view('settings.index', compact('settings'));
    }

    public function account()
    {
        $user = Auth::user();

        return view('settings.account', compact('user'));
    }

    public function payments()
    {
        $settings = $this->clinicSettings();

        return view('settings.payments', compact('settings'));
    }

    public function notifications()
    {
        $settings = $this->clinicSettings();

        return view('settings.notifications', compact('settings'));
    }

    public function security()
    {
        return view('settings.security');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => ['nullable', 'string', 'max:150'],
            'clinic_phone' => ['nullable', 'string', 'max:30'],
            'clinic_email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                [
                    'clinic_id' => Auth::user()->clinic_id,
                    'setting_key' => $key,
                ],
                [
                    'setting_value' => $value,
                ]
            );
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    private function clinicSettings()
    {
        return Setting::where(
            'clinic_id',
            Auth::user()->clinic_id
        )->get()->keyBy('setting_key');
    }
}