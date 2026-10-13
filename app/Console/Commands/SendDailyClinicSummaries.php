<?php

namespace App\Console\Commands;

use App\Mail\DailyClinicSummary;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDailyClinicSummaries extends Command
{
    protected $signature = 'clinics:daily-summary
        {--clinic= : Process one clinic ID}
        {--date= : Summary date in YYYY-MM-DD}
        {--send : Send email instead of only previewing totals}';

    protected $description =
        'Preview or send daily summaries for enabled clinics.';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | Summary Date — Yesterday by Default
        |--------------------------------------------------------------------------
        */

        $dateInput = $this->option('date');

        if (
            $dateInput &&
            ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)
        ) {
            $this->error('Use --date=YYYY-MM-DD.');

            return self::FAILURE;
        }

        try {
            $day = $dateInput
                ? CarbonImmutable::createFromFormat(
                    '!Y-m-d',
                    $dateInput,
                    'Asia/Dubai'
                )
                : CarbonImmutable::now('Asia/Dubai')
                    ->subDay()
                    ->startOfDay();

            if (
                $dateInput &&
                $day->format('Y-m-d') !== $dateInput
            ) {
                $this->error('Enter a valid calendar date.');

                return self::FAILURE;
            }
        } catch (Throwable $exception) {
            $this->error('Enter a valid calendar date.');

            return self::FAILURE;
        }

        $clinicOption = $this->option('clinic');

        if (
            $clinicOption !== null &&
            (! ctype_digit((string) $clinicOption) ||
                (int) $clinicOption < 1)
        ) {
            $this->error('Enter a valid clinic ID.');

            return self::FAILURE;
        }

        $start = $day->format('Y-m-d H:i:s');
        $end = $day->addDay()->format('Y-m-d H:i:s');

        $query = DB::table('clinics')
            ->where('onboarding_status', 'approved');

        if ($clinicOption !== null) {
            $query->where('clinic_id', (int) $clinicOption);
        }

        $clinics = $query->orderBy('clinic_id')->get();

        $processed = 0;
        $failed = 0;

        foreach ($clinics as $clinic) {
            $settings = DB::table('settings')
                ->where('clinic_id', $clinic->clinic_id)
                ->pluck('setting_value', 'setting_key');

            if (
                (string) $settings->get(
                    'daily_summary_enabled',
                    '0'
                ) !== '1'
            ) {
                continue;
            }

            if (! $this->hasValidLicense($clinic)) {
                $this->warn(
                    "Clinic #{$clinic->clinic_id}: skipped — no valid license."
                );

                continue;
            }

            $email = trim(
                (string) $settings->get('notification_email', '')
            );

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn(
                    "Clinic #{$clinic->clinic_id}: skipped — invalid summary email."
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Clinic-Specific Daily Totals
            |--------------------------------------------------------------------------
            */

            $visits = DB::table('visits')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('visit_date', '>=', $start)
                ->where('visit_date', '<', $end)
                ->count();

            $received = DB::table('payments')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('payment_date', '>=', $start)
                ->where('payment_date', '<', $end)
                ->sum('amount');

            $expenses = DB::table('expenses')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('expense_date', '>=', $start)
                ->where('expense_date', '<', $end)
                ->sum('amount');

            $summary = [
                'clinic_name' => $clinic->name,
                'date_label' => $day->format('d M Y'),
                'visits' => $visits,
                'received' => $received,
                'expenses' => $expenses,
                'net' => (float) $received - (float) $expenses,
                'currency' => strtoupper($clinic->currency ?: 'AED'),

                'decimal_places' =>
                    (string) $settings->get('decimal_places', '2') === '0'
                        ? 0
                        : 2,

                'dashboard_url' => route('dashboard'),
                'preferences_url' => route('settings.notifications'),
            ];

            $this->table(
                ['Clinic', 'Date', 'Visits', 'Received', 'Expenses'],
                [[
                    $clinic->name,
                    $summary['date_label'],
                    $visits,
                    $summary['currency'] . ' ' . $received,
                    $summary['currency'] . ' ' . $expenses,
                ]]
            );

            if ($this->option('send')) {
                try {
                    Mail::to($email)->send(
                        new DailyClinicSummary($summary)
                    );

                    $this->info(
                        "Summary sent using the configured mailer: {$email}"
                    );
                } catch (Throwable $exception) {
                    report($exception);

                    $this->error(
                        "Clinic #{$clinic->clinic_id}: email sending failed."
                    );

                    $failed++;

                    continue;
                }
            }

            $processed++;
        }

        if ($processed === 0 && $failed === 0) {
            $this->warn(
                'No eligible clinics found. Check approval, license, daily summary toggle and email.'
            );
        }

        if (! $this->option('send')) {
            $this->info('Preview only. No emails were sent.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Approved Clinic — Current License Eligibility
    |--------------------------------------------------------------------------
    */

    private function hasValidLicense(object $clinic): bool
    {
        // Preserve the existing legacy-clinic exemption.
        if ($clinic->saas_customer_id === null) {
            return true;
        }

        if (! $clinic->current_license_id) {
            return false;
        }

        return DB::table('clinic_licenses as licenses')
            ->join(
                'subscription_plans as plans',
                'plans.subscription_plan_id',
                '=',
                'licenses.subscription_plan_id'
            )
            ->where(
                'licenses.clinic_license_id',
                $clinic->current_license_id
            )
            ->where('licenses.clinic_id', $clinic->clinic_id)
            ->where('licenses.status', 'active')
            ->where('licenses.starts_at', '<=', now())
            ->where(function ($query) {
                $query
                    ->where('licenses.ends_at', '>', now())
                    ->orWhere(function ($query) {
                        $query
                            ->where('plans.term_type', 'lifetime')
                            ->whereNull('licenses.ends_at');
                    });
            })
            ->exists();
    }
}