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
    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;

        $query = User::where(
            'clinic_id',
            $clinicId
        )->with('role');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $users = $query
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view(
            'users.create',
            compact('roles')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
    'name' => [
        'required',
        'string',
        'max:255',
    ],

    'email' => [
        'required',
        'email',
        'max:255',
        'unique:users,email',
    ],

    'role_id' => [
        'required',
        'integer',
        'exists:roles,role_id',
    ],

    'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
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
            'password' => Hash::make(
                $validated['password']
            ),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User created successfully.'
            );
    }

    public function show(User $user)
    {
        $this->ensureClinicAccess($user);

        $user->load('role');

        return view(
            'users.show',
            compact('user')
        );
    }

    public function edit(User $user)
    {
        $this->ensureClinicAccess($user);

        $roles = Role::orderBy('name')->get();

        return view(
            'users.create',
            compact('user', 'roles')
        );
    }

    public function update(
        Request $request,
        User $user
    ) {
        $this->ensureClinicAccess($user);

        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                'exists:roles,role_id',
            ],

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

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $user->role_id = $validated['role_id'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active =
            $validated['is_active'] ?? false;

        if (! empty($validated['password'])) {
            $user->password = Hash::make(
                $validated['password']
            );
        }

        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                'User updated successfully.'
            );
    }

    public function destroy(User $user)
    {
        $this->ensureClinicAccess($user);

        abort_if(
            $user->user_id === Auth::id(),
            403,
            'You cannot delete your own account.'
        );

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User deleted successfully.'
            );
    }

    private function ensureClinicAccess(User $user)
    {
        abort_if(
            $user->clinic_id !== Auth::user()->clinic_id,
            403
        );
    }
}