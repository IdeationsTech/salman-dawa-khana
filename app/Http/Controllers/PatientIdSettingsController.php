<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientIdSettingsController extends Controller
{
    public function update(Request $request)
    {
        abort_unless(
            $request->user()->role?->name === 'Owner',
            403
        );

        $validated = $request->validate([
            'patient_id_country' => [
                'required',
                Rule::in(['AE', 'PK']),
            ],
        ]);

        Setting::updateOrCreate(
            [
                'clinic_id' => $request->user()->clinic_id,
                'setting_key' => 'patient_id_country',
            ],
            [
                'setting_value' => $validated['patient_id_country'],
            ]
        );

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Patient ID country updated. Existing patient IDs remain unchanged.'
            );
    }
}