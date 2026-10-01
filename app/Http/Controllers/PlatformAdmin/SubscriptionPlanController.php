<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = DB::table('subscription_plans')
            ->whereIn('plan_code', [
                'trial_7d',
                'monthly',
                'yearly',
                'lifetime',
            ])
            ->orderBy('sort_order')
            ->get();

        return view('platform-admin.plans.index', [
            'plans' => $plans,
        ]);
    }

    public function update(
        Request $request,
        int $plan
    ): RedirectResponse {
        $validated = $request->validate([
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'decimal:0,2',
            ],
            'is_active' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use (
            $request,
            $plan,
            $validated
        ) {
            $existing = DB::table('subscription_plans')
                ->where('subscription_plan_id', $plan)
                ->lockForUpdate()
                ->first();

            abort_if(! $existing, 404);

            if (
                ! in_array(
                    $existing->plan_code,
                    ['monthly', 'yearly', 'lifetime'],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'price' =>
                        'Only Monthly, Yearly and Lifetime plans can be priced here.',
                ]);
            }

            $newValues = [
                'price' => number_format(
                    (float) $validated['price'],
                    2,
                    '.',
                    ''
                ),
                'is_active' =>
                    $validated['is_active'] === '1',
                'description' =>
                    $validated['description'] ?? null,
            ];

            DB::table('subscription_plans')
                ->where('subscription_plan_id', $plan)
                ->update([
                    ...$newValues,
                    'updated_at' => now(),
                ]);

            DB::table('platform_audit_logs')->insert([
                'platform_admin_id' =>
                    Auth::guard('platform_admin')->id(),
                'clinic_id' => null,
                'action' => 'subscription_plan.updated',
                'entity_type' => 'subscription_plans',
                'entity_id' => $plan,
                'old_values' => json_encode([
                    'price' => $existing->price,
                    'is_active' => (bool) $existing->is_active,
                    'description' => $existing->description,
                ], JSON_THROW_ON_ERROR),
                'new_values' => json_encode(
                    $newValues,
                    JSON_THROW_ON_ERROR
                ),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit(
                    (string) $request->userAgent(),
                    500,
                    ''
                ),
                'created_at' => now(),
            ]);
        }, 3);

        return redirect()
            ->route('platform-admin.plans.index')
            ->with('success', 'Subscription plan updated.');
    }
}