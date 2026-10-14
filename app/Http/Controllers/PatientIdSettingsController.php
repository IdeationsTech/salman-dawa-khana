<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\ClinicLocaleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientIdSettingsController extends Controller
{
    public function update(Request $request)
    {
        abort_unless($request->user()->role?->name === 'Owner', 403);

        $data = $request->validate([
            'patient_id_country' => [
                'required',
                Rule::in(array_keys(ClinicLocaleCatalog::idLabels())),
            ],
        ]);

        Setting::updateOrCreate([
            'clinic_id' => $request->user()->clinic_id,
            'setting_key' => 'patient_id_country',
        ], [
            'setting_value' => $data['patient_id_country'],
        ]);

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Patient ID format updated. Existing patients retain their saved ID format.'
            );
    }
}