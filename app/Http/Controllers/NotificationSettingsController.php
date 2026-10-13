<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Notifications — Save Clinic Preferences
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $validated = $request->validate([
            'notification_email' => [
                'nullable',
                'email',
                'max:160',
                'required_if:daily_summary_enabled,1',
            ],

            'in_app_enabled' => ['required', 'boolean'],
            'payment_alert_enabled' => ['required', 'boolean'],
            'patient_alert_enabled' => ['required', 'boolean'],
            'daily_summary_enabled' => ['required', 'boolean'],
        ], [
            'notification_email.required_if' =>
                'Enter an email address to receive the daily summary.',
        ]);

        $clinicId = (int) $request->user()->clinic_id;

        DB::transaction(function () use (
            $validated,
            $clinicId
        ) {
            $clinic = DB::table('clinics')
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->first();

            abort_unless($clinic, 404);

            Setting::updateOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'setting_key' => 'notification_email',
                ],
                [
                    'setting_value' =>
                        $validated['notification_email'] ?? null,
                ]
            );

            foreach ([
                'in_app_enabled',
                'payment_alert_enabled',
                'patient_alert_enabled',
                'daily_summary_enabled',
            ] as $key) {
                Setting::updateOrCreate(
                    [
                        'clinic_id' => $clinicId,
                        'setting_key' => $key,
                    ],
                    [
                        'setting_value' =>
                            (string) (int) $validated[$key],
                    ]
                );
            }
        });

        return redirect()
            ->route('settings.notifications')
            ->with(
                'success',
                'Notification preferences updated successfully.'
            );
    }
}