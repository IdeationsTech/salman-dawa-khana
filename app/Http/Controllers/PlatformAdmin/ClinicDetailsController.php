<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ClinicDetailsController extends Controller
{
    public function update(
        Request $request,
        int $clinic
    ): RedirectResponse {
        abort_unless(
            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->exists(),
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:160',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:max_width=3000,max_height=3000',
                'prohibited_if:remove_logo,1',
            ],
            'remove_logo' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $newLogoPath = null;
        $oldLogoPath = null;

        try {
            if ($request->hasFile('logo')) {
                $newLogoPath = $request->file('logo')->store(
                    "clinics/{$clinic}/logos",
                    'public'
                );

                if (! $newLogoPath) {
                    throw ValidationException::withMessages([
                        'logo' => 'The logo could not be uploaded.',
                    ]);
                }
            }

            DB::transaction(function () use (
                $request,
                $clinic,
                $validated,
                $newLogoPath,
                &$oldLogoPath
            ) {
                $existing = DB::table('clinics')
                    ->where('clinic_id', $clinic)
                    ->lockForUpdate()
                    ->first();

                abort_if(! $existing, 404);

                $oldLogoPath = $existing->logo_path;

                $logoPath = $newLogoPath
                    ?? $existing->logo_path;

                if ($request->boolean('remove_logo')) {
                    $logoPath = null;
                }

                $newValues = [
                    'name' => trim($validated['name']),
                    'phone' => trim($validated['phone']),
                    'address' => filled($validated['address'] ?? null)
                        ? trim($validated['address'])
                        : null,
                    'logo_path' => $logoPath,
                ];

                if (
                    $newValues['name'] === '' ||
                    $newValues['phone'] === ''
                ) {
                    throw ValidationException::withMessages([
                        'name' =>
                            'Clinic name and phone cannot be blank.',
                    ]);
                }

                DB::table('clinics')
                    ->where('clinic_id', $clinic)
                    ->update([
                        ...$newValues,
                        'updated_at' => now(),
                    ]);

                DB::table('platform_audit_logs')->insert([
                    'platform_admin_id' =>
                        Auth::guard('platform_admin')->id(),
                    'clinic_id' => $clinic,
                    'action' => 'clinic.details_updated',
                    'entity_type' => 'clinics',
                    'entity_id' => $clinic,
                    'old_values' => json_encode([
                        'name' => $existing->name,
                        'phone' => $existing->phone,
                        'address' => $existing->address,
                        'logo_path' => $existing->logo_path,
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
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                try {
                    Storage::disk('public')->delete($newLogoPath);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        // Remove the previous uploaded logo after a successful save.
        if (
            $oldLogoPath &&
            ($newLogoPath || $request->boolean('remove_logo')) &&
            Str::startsWith(
                $oldLogoPath,
                "clinics/{$clinic}/logos/"
            )
        ) {
            try {
                Storage::disk('public')->delete($oldLogoPath);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('platform-admin.clinics.edit', $clinic)
            ->with('success', 'Clinic details updated.');
    }
}