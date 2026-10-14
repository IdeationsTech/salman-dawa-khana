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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'status' => [
                'nullable',
                Rule::in(['all', 'active', 'inactive']),
            ],
        ]);

        $clinicId = (int) Auth::user()->clinic_id;

        $query = User::query()
            ->where('clinic_id', $clinicId)
            ->with('role');

        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $query->orWhere('user_id', (int) $search);
                }
            });
        }

        $status = $filters['status'] ?? 'all';

        if ($status !== 'all') {
            $query->where('is_active', $status === 'active');
        }

        $users = $query
            ->orderByDesc('created_at')
            ->orderByDesc('user_id')
            ->paginate(50)
            ->withQueryString();

        $base = User::query()->where('clinic_id', $clinicId);

        $totalUsers = (clone $base)->count();

        $activeUsers = (clone $base)
            ->where('is_active', true)
            ->count();

        $doctorUsers = (clone $base)
            ->whereHas('role', function ($query) {
                $query->where('name', 'Doctor');
            })
            ->count();

        $roleCount = Role::query()
            ->whereIn('name', ['Owner', 'Doctor', 'Receptionist'])
            ->count();

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
        $roles = Role::whereIn('name', [
            'Owner',
            'Doctor',
            'Receptionist',
        ])
            ->orderBy('name')
            ->get();

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

            'phone' => [
                'bail',
                'required',
                'string',
                'max:16',
                new \App\Rules\InternationalPhone(),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'role_id')->whereIn('name', [
                    'Owner',
                    'Doctor',
                    'Receptionist',
                ]),
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
            'phone' => $validated['phone'] ?? null,
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

        $roles = Role::whereIn('name', [
            'Owner',
            'Doctor',
            'Receptionist',
        ])
            ->orderBy('name')
            ->get();

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

            'phone' => [
                'bail',
                'required',
                'string',
                'max:16',
                new \App\Rules\InternationalPhone(),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'role_id')->whereIn('name', [
                    'Owner',
                    'Doctor',
                    'Receptionist',
                ]),
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

        /*
|--------------------------------------------------------------------------
| Keep the Signed-In Owner's Role
|--------------------------------------------------------------------------
*/

        if (
            (int) $user->user_id === (int) Auth::id() &&
            (int) $validated['role_id'] !== (int) $user->role_id
        ) {
            return back()
                ->withErrors([
                    'role_id' =>
                        'You cannot change your own role.',
                ])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]));
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
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

        $clinicId = (int) Auth::user()->clinic_id;

        try {
            $deleted = \Illuminate\Support\Facades\DB::transaction(
                function () use ($user, $clinicId) {
                    $locked = User::query()
                        ->where('clinic_id', $clinicId)
                        ->whereKey($user->user_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Users Referenced by Historical Records
                    |--------------------------------------------------------------------------
                    */

                    $references = [
                        'visits' => 'recorded_by_user_id',
                        'prescriptions' => 'created_by_user_id',
                        'bills' => 'created_by_user_id',
                        'payments' => 'received_by_user_id',
                        'expenses' => 'recorded_by_user_id',
                        'ledger_entries' => 'created_by_user_id',
                        'audit_logs' => 'user_id',
                        'nuskha_orders' => 'created_by_user_id',
                        'nuskha_order_payments' => 'received_by_user_id',
                    ];

                    foreach ($references as $table => $column) {
                        $hasRecords = \Illuminate\Support\Facades\DB::table($table)
                            ->where($column, $locked->user_id)
                            ->exists();

                        if ($hasRecords) {
                            return false;
                        }
                    }

                    $locked->delete();

                    return true;
                },
                3
            );
        } catch (\Illuminate\Database\QueryException $exception) {
            // Handle an additional/concurrent foreign-key reference safely.
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            $deleted = false;
        }

        if (! $deleted) {
            return redirect()
                ->route('users.edit', $user)
                ->withErrors([
                    'user' =>
                        'This user has linked records and cannot be deleted. '
                        . 'Turn off Active account and save to deactivate them instead.',
                ]);
        }

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