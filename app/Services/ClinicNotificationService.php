<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ClinicNotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Notifications — New Patient
    |--------------------------------------------------------------------------
    */

    public function patientAdded(Patient $patient): void
    {
        $this->publish(
            clinicId: (int) $patient->clinic_id,
            type: 'patient_registered',
            preference: 'patient_alert_enabled',
            eventKey: 'patient.created.' . $patient->patient_id,
            title: 'New patient registered',
            message: $patient->full_name . ' has been registered.',
            routeName: 'patients.show',
            routeParameters: [
                'patient' => (int) $patient->patient_id,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications — Payment Received
    |--------------------------------------------------------------------------
    */

    public function paymentReceived(Payment $payment): void
    {
        $patient = Patient::query()
            ->where('clinic_id', $payment->clinic_id)
            ->where('patient_id', $payment->patient_id)
            ->first();

        if (! $patient) {
            return;
        }

        $this->publish(
            clinicId: (int) $payment->clinic_id,
            type: 'payment_received',
            preference: 'payment_alert_enabled',
            eventKey: 'payment.received.' . $payment->payment_id,
            title: 'Patient payment received',
            message: 'Payment PAY-' . $payment->payment_id
                . ' has been recorded for ' . $patient->full_name . '.',
            routeName: 'payments.show',
            routeParameters: [
                'payment' => (int) $payment->payment_id,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications — Settings and Duplicate Protection
    |--------------------------------------------------------------------------
    */

    private function publish(
        int $clinicId,
        string $type,
        string $preference,
        string $eventKey,
        string $title,
        string $message,
        string $routeName,
        array $routeParameters
    ): void {
        $settings = Setting::query()
            ->where('clinic_id', $clinicId)
            ->whereIn('setting_key', [
                'in_app_enabled',
                $preference,
            ])
            ->pluck('setting_value', 'setting_key');

        if (
            (string) $settings->get('in_app_enabled', '1') !== '1' ||
            (string) $settings->get($preference, '1') !== '1'
        ) {
            return;
        }

        DB::table('clinic_notifications')->upsert(
            [
                [
                    'clinic_id' => $clinicId,
                    'type' => $type,
                    'event_key' => $eventKey,
                    'title' => $title,
                    'message' => $message,
                    'route_name' => $routeName,
                    'route_parameters' => json_encode(
                        $routeParameters,
                        JSON_THROW_ON_ERROR
                    ),
                    'created_at' => now(),
                ],
            ],
            ['clinic_id', 'event_key'],
            ['event_key']
        );
    }
}