<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Users — List and Filters
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = User::where('clinic_id', $clinicId)
            ->with('role');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array(
            $request->input('status'),
            ['active', 'inactive'],
            true
        )) {
            $query->where(
                'is_active',
                $request->input('status') === 'active'
            );
        }

        $users = $query
            ->orderByDesc('created_at')
            ->orderByDesc('user_id')
            ->paginate(50)
            ->withQueryString();

        $totalUsers = User::where('clinic_id', $clinicId)
            ->count();

        $activeUsers = User::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->count();

        $doctorUsers = User::where('clinic_id', $clinicId)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Doctor');
            })
            ->count();

        $roleCount = Role::count();

        return view('users.index', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'doctorUsers',
            'roleCount'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Create Form
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Save New User
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique('users', 'email'),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'role_id'),
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

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        User::create([
            'clinic_id' => Auth::user()->clinic_id,
            'role_id' => $validated['role_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Users — View
    |--------------------------------------------------------------------------
    */

    public function show(User $user)
    {
        $this->ensureClinicAccess($user);

        $user->load('role');

        return view('users.show', compact('user'));
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Edit Form
    |--------------------------------------------------------------------------
    */

    public function edit(User $user)
    {
        $this->ensureClinicAccess($user);

        $roles = Role::orderBy('name')->get();

        return view(
            'users.create',
            compact('user', 'roles')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Update
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $user)
    {
        $this->ensureClinicAccess($user);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique('users', 'email')
                    ->ignore($user->user_id, 'user_id'),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'role_id'),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if ($value !== null && strlen($value) > 72) {
                        $fail('The password must not exceed 72 bytes.');
                    }
                },
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (
            (int) $user->user_id === (int) Auth::id() &&
            ! $request->boolean('is_active')
        ) {
            return back()
                ->withErrors([
                    'is_active' =>
                        'You cannot deactivate your own account.',
                ])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]));
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->is_active = $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        $this->ensureClinicAccess($user);

        abort_if(
            (int) $user->user_id === (int) Auth::id(),
            403,
            'You cannot delete your own account.'
        );

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Users — Clinic Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureClinicAccess(User $user): void
    {
        abort_unless(
            (int) $user->clinic_id ===
                (int) Auth::user()->clinic_id,
            404
        );
    }
}