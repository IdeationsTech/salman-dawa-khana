<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentSettingsController extends Controller
{
    public function update(Request $request)
    {
        abort_unless($request->user()->role?->name === 'Owner', 403);

        $data = $request->validate([
            'currency' => ['prohibited'],
            'country_code' => ['prohibited'],

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
        ], [
            'currency.prohibited' =>
                'Only the platform administrator can change clinic currency.',
            'country_code.prohibited' =>
                'Only the platform administrator can change clinic country.',
        ]);

        if (
            !$data['cash_enabled']
            && !$data['bank_enabled']
            && !$data['card_enabled']
        ) {
            throw ValidationException::withMessages([
                'cash_enabled' => 'Keep at least one payment method enabled.',
            ]);
        }

        $clinicId = (int) $request->user()->clinic_id;

        DB::transaction(function () use ($data, $clinicId) {
            $clinic = DB::table('clinics')
                ->where('clinic_id', $clinicId)
                ->lockForUpdate()
                ->first();

            abort_unless($clinic, 404);

            foreach ($data as $key => $value) {
                if (in_array($key, ['currency', 'country_code'], true)) {
                    continue;
                }

                Setting::updateOrCreate([
                    'clinic_id' => $clinicId,
                    'setting_key' => $key,
                ], [
                    'setting_value' => (string) (int) $value,
                ]);
            }
        }, 3);

        return redirect()
            ->route('settings.payments')
            ->with('success', 'Payment settings updated successfully.');
    }
}