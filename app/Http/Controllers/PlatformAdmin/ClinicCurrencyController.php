<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Support\ClinicLocaleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClinicCurrencyController extends Controller
{
    public function edit(int $clinic)
    {
        $record = DB::table('clinics')
            ->where('clinic_id', $clinic)
            ->first();

        abort_unless($record, 404);

        $history = [];

        foreach (['bills', 'payments', 'expenses', 'nuskha_orders'] as $table) {
            $history[$table] = DB::table($table)
                ->where('clinic_id', $clinic)
                ->select('currency')
                ->selectRaw('COUNT(*) AS record_count')
                ->groupBy('currency')
                ->orderBy('currency')
                ->get();
        }

        return view('platform-admin.clinics.currency', [
            'clinic' => $record,
            'countries' => ClinicLocaleCatalog::countries(),
            'currencies' => ClinicLocaleCatalog::currencies(),
            'history' => $history,
        ]);
    }

    public function update(Request $request, int $clinic)
    {
        $data = $request->validate([
            'country_code' => [
                'required',
                Rule::in(array_keys(ClinicLocaleCatalog::countries())),
            ],
            'currency' => [
                'required',
                Rule::in(array_keys(ClinicLocaleCatalog::currencies())),
            ],
            'expected_currency' => ['required', 'string', 'size:3'],
            'expected_country' => ['nullable', 'string', 'size:2'],
            'reason' => ['required', 'string', 'max:2000'],
            'client_consulted' => ['accepted'],
            'history_acknowledged' => ['accepted'],
        ]);

        DB::transaction(function () use ($request, $clinic, $data) {
            $record = DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->lockForUpdate()
                ->first();

            abort_unless($record, 404);

            if (
                $record->currency !== $data['expected_currency']
                || ($record->country_code ?? '') !== ($data['expected_country'] ?? '')
            ) {
                throw ValidationException::withMessages([
                    'currency' =>
                        'Clinic country or currency changed while this page was open. Reload and review again.',
                ]);
            }

            if (
                $record->currency === $data['currency']
                && $record->country_code === $data['country_code']
            ) {
                throw ValidationException::withMessages([
                    'currency' => 'No country or currency change was selected.',
                ]);
            }

            $newValues = [
                'country_code' => $data['country_code'],
                'currency' => $data['currency'],
            ];

            DB::table('clinics')
                ->where('clinic_id', $clinic)
                ->update([...$newValues, 'updated_at' => now()]);

            DB::table('platform_audit_logs')->insert([
                'platform_admin_id' => Auth::guard('platform_admin')->id(),
                'clinic_id' => $clinic,
                'action' => 'clinic.country_currency_changed',
                'entity_type' => 'clinics',
                'entity_id' => $clinic,
                'old_values' => json_encode([
                    'country_code' => $record->country_code,
                    'currency' => $record->currency,
                ], JSON_THROW_ON_ERROR),
                'new_values' => json_encode([
                    ...$newValues,
                    'reason' => $data['reason'],
                    'client_consulted' => true,
                    'history_acknowledged' => true,
                ], JSON_THROW_ON_ERROR),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'created_at' => now(),
            ]);
        }, 3);

        return redirect()
            ->route('platform-admin.clinics.edit', $clinic)
            ->with(
                'success',
                'Clinic country/currency updated. Historical amounts and patient ID formats were preserved.'
            );
    }
}