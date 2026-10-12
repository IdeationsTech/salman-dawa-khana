<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Payment Settings — Save Clinic Preferences
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $validated = $request->validate([
            'currency' => [
                'required',
                Rule::in(['AED', 'PKR', 'USD']),
            ],

            'decimal_places' => [
                'required',
                'integer',
                Rule::in([0, 2]),
            ],

            'cash_enabled' => ['required', 'boolean'],
            'bank_enabled' => ['required', 'boolean'],
            'card_enabled' => ['required', 'boolean'],
            'show_previous_due' => ['required', 'boolean'],
            'allow_partial_payments' => ['required', 'boolean'],
        ]);

        if (
            ! $request->boolean('cash_enabled') &&
            ! $request->boolean('bank_enabled') &&
            ! $request->boolean('card_enabled')
        ) {
            throw ValidationException::withMessages([
                'cash_enabled' =>
                    'Keep at least one payment method enabled.',
            ]);
        }

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

            DB::table('clinics')
                ->where('clinic_id', $clinicId)
                ->update([
                    'currency' => $validated['currency'],
                    'updated_at' => now(),
                ]);

            foreach ([
                'decimal_places',
                'cash_enabled',
                'bank_enabled',
                'card_enabled',
                'show_previous_due',
                'allow_partial_payments',
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
            ->route('settings.payments')
            ->with(
                'success',
                'Payment settings updated successfully.'
            );
    }
}