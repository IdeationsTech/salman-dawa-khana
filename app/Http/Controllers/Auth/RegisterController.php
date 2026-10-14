<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\SaasCustomer;
use App\Models\Setting;
use App\Models\User;
use App\Rules\InternationalPhone;
use App\Support\ClinicLocaleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', [
            'countries' => ClinicLocaleCatalog::countries(),
            'currencies' => ClinicLocaleCatalog::currencies(),
            'countryCurrencies' => ClinicLocaleCatalog::defaults(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'clinic_name' => ['required', 'string', 'max:160'],
            'owner_name' => ['required', 'string', 'max:120'],

            'country_code' => [
                'required',
                Rule::in(array_keys(ClinicLocaleCatalog::countries())),
            ],

            'currency' => [
                'required',
                Rule::in(array_keys(ClinicLocaleCatalog::currencies())),
            ],

            'email' => [
                'required',
                'email',
                'max:160',
                'unique:users,email',
            ],

            'phone' => [
                'bail',
                'required',
                'string',
                'max:16',
                new InternationalPhone(),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (strlen($value) > 72) {
                        $fail('The password must not exceed 72 bytes.');
                    }
                },
            ],

            'terms' => ['accepted'],
        ]);

        DB::transaction(function () use ($data) {
            $ownerRole = Role::where('name', 'Owner')->firstOrFail();

            $customer = SaasCustomer::create([
                'contact_name' => $data['owner_name'],
                'company_name' => $data['clinic_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country_code' => $data['country_code'],
                'status' => 'active',
            ]);

            $clinic = new Clinic();

            $clinic->name = $data['clinic_name'];
            $clinic->phone = $data['phone'];
            $clinic->country_code = $data['country_code'];
            $clinic->currency = $data['currency'];
            $clinic->saas_customer_id = $customer->saas_customer_id;
            $clinic->onboarding_status = 'pending_review';

            $clinic->save();

            Setting::create([
                'clinic_id' => $clinic->clinic_id,
                'setting_key' => 'patient_id_country',
                'setting_value' => ClinicLocaleCatalog::idCountry(
                    $data['country_code']
                ),
            ]);

            User::create([
                'clinic_id' => $clinic->clinic_id,
                'role_id' => $ownerRole->role_id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => false,
            ]);
        }, 3);

        return redirect()->route('registration.pending');
    }
}