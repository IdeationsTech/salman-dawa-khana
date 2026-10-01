<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        $clinic = DB::table('clinics')
            ->where('clinic_id', $user->clinic_id)
            ->first();

        if (! $clinic || $clinic->onboarding_status !== 'approved') {
            abort(403, 'Clinic access is currently unavailable.');
        }

        /*
         * Existing clinics created before SaaS onboarding may not yet
         * have a license. Their access stays available during migration.
         * New SaaS clinics require a current valid license.
         */
        if ($clinic->saas_customer_id !== null) {
            if (! $clinic->current_license_id) {
                abort(403, 'No current clinic license is assigned.');
            }

            $license = DB::table('clinic_licenses')
                ->where(
                    'clinic_license_id',
                    $clinic->current_license_id
                )
                ->where('clinic_id', $clinic->clinic_id)
                ->first();

            if (
                ! $license ||
                $license->status !== 'active' ||
                $license->starts_at > now() ||
                ($license->ends_at !== null && $license->ends_at <= now())
            ) {
                abort(403, 'Your clinic license is not active.');
            }
        }

        return $next($request);
    }
}