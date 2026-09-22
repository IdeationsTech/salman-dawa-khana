<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $clinic = Clinic::create([
                'name' => $validated['clinic_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            $ownerRole = Role::where('name', 'Owner')->firstOrFail();

            return User::create([
                'clinic_id' => $clinic->clinic_id,
                'role_id' => $ownerRole->role_id,
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);
        });

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}