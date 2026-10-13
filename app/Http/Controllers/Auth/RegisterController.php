<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\SaasCustomer;
use App\Models\User;
use App\Rules\InternationalPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use libphonenumber\PhoneNumberUtil;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    /*
    |--------------------------------------------------------------------------
    | Registration — Validated International Phone
    |--------------------------------------------------------------------------
    */

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(
                trim((string) $request->input('email'))
            ),
        ]);

        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:160'],
            'owner_name' => ['required', 'string', 'max:120'],

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

        $phoneUtil = PhoneNumberUtil::getInstance();

        $phoneCountry = $phoneUtil->getRegionCodeForNumber(
            $phoneUtil->parse($validated['phone'], null)
        );

        DB::transaction(function () use (
            $validated,
            $phoneCountry
        ) {
            $customer = SaasCustomer::create([
                'contact_name' => $validated['owner_name'],
                'company_name' => $validated['clinic_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],

                // Derived from the phone number; not a residence claim.
                'country_code' => $phoneCountry,
                'status' => 'active',
            ]);

            $clinic = new Clinic();

            $clinic->name = $validated['clinic_name'];
            $clinic->phone = $validated['phone'];
            $clinic->currency = 'AED';
            $clinic->saas_customer_id = $customer->saas_customer_id;
            $clinic->onboarding_status = 'pending_review';

            $clinic->save();

            $ownerRole = Role::where('name', 'Owner')
                ->firstOrFail();

            User::create([
                'clinic_id' => $clinic->clinic_id,
                'role_id' => $ownerRole->role_id,
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_active' => false,
            ]);
        });

        return redirect()->route('registration.pending');
    }
}