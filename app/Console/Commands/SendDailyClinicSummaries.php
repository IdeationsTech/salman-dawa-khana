<?php

namespace App\Console\Commands;

use App\Mail\DailyClinicSummary;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendDailyClinicSummaries extends Command
{
    protected $signature = 'clinics:daily-summary
        {--clinic= : Process one clinic ID}
        {--date= : Summary date in YYYY-MM-DD}
        {--send : Send instead of previewing}
        {--retry-failed : Retry previously failed deliveries}';

    protected $description =
        'Preview or send currency-aware clinic summaries with delivery tracking.';

    public function handle(): int
    {
        $dateInput = $this->option('date');

        try {
            if (
                $dateInput &&
                ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)
            ) {
                throw new \InvalidArgumentException();
            }

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
                ! $day ||
                ($dateInput && $day->format('Y-m-d') !== $dateInput)
            ) {
                throw new \InvalidArgumentException();
            }
        } catch (Throwable $exception) {
            $this->error('Use a valid --date=YYYY-MM-DD.');

            return self::FAILURE;
        }

        $clinicOption = $this->option('clinic');

        if (
            $clinicOption !== null &&
            (
                ! ctype_digit((string) $clinicOption) ||
                (int) $clinicOption < 1
            )
        ) {
            $this->error('Enter a valid clinic ID.');

            return self::FAILURE;
        }

        $mailer = (string) config('mail.default');

        $channel = in_array(
            config("mail.mailers.{$mailer}.transport"),
            ['log', 'array'],
            true
        ) ? 'local-test' : 'email';

        $start = $day->format('Y-m-d H:i:s');
        $end = $day->addDay()->format('Y-m-d H:i:s');

        $query = DB::table('clinics')
            ->where('onboarding_status', 'approved');

        if ($clinicOption !== null) {
            $query->where('clinic_id', (int) $clinicOption);
        }

        $processed = 0;
        $failed = 0;

        foreach ($query->orderBy('clinic_id')->get() as $clinic) {
            $settings = DB::table('settings')
                ->where('clinic_id', $clinic->clinic_id)
                ->pluck('setting_value', 'setting_key');

            if (
                (string) $settings->get('daily_summary_enabled', '0') !== '1' ||
                ! $this->hasValidLicense($clinic)
            ) {
                continue;
            }

            $email = trim(
                (string) $settings->get('notification_email', '')
            );

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn(
                    "Clinic #{$clinic->clinic_id}: invalid summary email."
                );

                continue;
            }

            $summaryCurrency = strtoupper($clinic->currency ?: 'AED');

            /*
            |--------------------------------------------------------------------------
            | Activity and Received Amounts — Clinic and Currency Scoped
            |--------------------------------------------------------------------------
            */

            $visits = DB::table('visits')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('visit_date', '>=', $start)
                ->where('visit_date', '<', $end)
                ->count();

            $patientReceived = DB::table('payments')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('currency', $summaryCurrency)
                ->where('payment_date', '>=', $start)
                ->where('payment_date', '<', $end)
                ->sum('amount');

            $orderReceived = DB::table('nuskha_order_payments as payments')
                ->join(
                    'nuskha_orders as orders',
                    'orders.nuskha_order_id',
                    '=',
                    'payments.nuskha_order_id'
                )
                ->where('payments.clinic_id', $clinic->clinic_id)
                ->where('orders.clinic_id', $clinic->clinic_id)
                ->where('orders.currency', $summaryCurrency)
                ->where('payments.paid_at', '>=', $start)
                ->where('payments.paid_at', '<', $end)
                ->sum('payments.amount');

            $expenseAmount = DB::table('expenses')
                ->where('clinic_id', $clinic->clinic_id)
                ->where('currency', $summaryCurrency)
                ->where('expense_date', '>=', $start)
                ->where('expense_date', '<', $end)
                ->sum('amount');

            $receivedCents =
                $this->toCents($patientReceived)
                + $this->toCents($orderReceived);

            $expenseCents = $this->toCents($expenseAmount);

            $received = $this->decimal($receivedCents);
            $expenses = $this->decimal($expenseCents);

            $summary = [
                'clinic_name' => $clinic->name,
                'date_label' => $day->format('d M Y'),
                'visits' => $visits,
                'received' => $received,
                'expenses' => $expenses,
                'net' => $this->decimal($receivedCents - $expenseCents),
                'currency' => $summaryCurrency,
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
                    $summaryCurrency . ' ' . $received,
                    $summaryCurrency . ' ' . $expenses,
                ]]
            );

            $this->line(
                "Financial totals include {$summaryCurrency} records only."
            );

            $processed++;

            if (! $this->option('send')) {
                continue;
            }

            $deliveryId = $this->claimDelivery(
                (int) $clinic->clinic_id,
                $day->format('Y-m-d'),
                $channel,
                $email
            );

            if ($deliveryId === null) {
                $this->warn(
                    'Skipped: already sent, processing, or failed without --retry-failed.'
                );

                continue;
            }

            try {
                Mail::to($email)->send(
                    new DailyClinicSummary($summary)
                );

                DB::table('clinic_summary_deliveries')
                    ->where('clinic_summary_delivery_id', $deliveryId)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'processing_started_at' => null,
                        'last_error' => null,
                        'updated_at' => now(),
                    ]);

                $this->info(
                    $channel === 'local-test'
                        ? "Summary generated with local test mailer: {$email}"
                        : "Summary submitted to email transport: {$email}"
                );
            } catch (Throwable $exception) {
                report($exception);

                try {
                    DB::table('clinic_summary_deliveries')
                        ->where('clinic_summary_delivery_id', $deliveryId)
                        ->update([
                            'status' => 'failed',
                            'processing_started_at' => null,
                            'last_error' => Str::limit(
                                $exception->getMessage(),
                                2000,
                                ''
                            ),
                            'updated_at' => now(),
                        ]);
                } catch (Throwable $trackingException) {
                    report($trackingException);
                }

                $this->error(
                    "Clinic #{$clinic->clinic_id}: delivery failed; review before retrying."
                );

                $failed++;
            }
        }

        if ($processed === 0) {
            $this->warn(
                'No eligible clinics found. Check approval, license, daily summary toggle and email.'
            );
        }

        if (! $this->option('send')) {
            $this->info('Preview only. No emails or delivery records created.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function claimDelivery(
        int $clinicId,
        string $date,
        string $channel,
        string $email
    ): ?int {
        return DB::transaction(function () use (
            $clinicId,
            $date,
            $channel,
            $email
        ) {
            DB::table('clinic_summary_deliveries')->insertOrIgnore([
                'clinic_id' => $clinicId,
                'summary_date' => $date,
                'delivery_channel' => $channel,
                'recipient_email' => $email,
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $delivery = DB::table('clinic_summary_deliveries')
                ->where('clinic_id', $clinicId)
                ->where('summary_date', $date)
                ->where('delivery_channel', $channel)
                ->lockForUpdate()
                ->first();

            if (! $delivery) {
                throw new \RuntimeException(
                    'The summary delivery record could not be created.'
                );
            }

            if (in_array($delivery->status, ['sent', 'processing'], true)) {
                return null;
            }

            if (
                $delivery->status === 'failed' &&
                ! $this->option('retry-failed')
            ) {
                return null;
            }

            if (! in_array($delivery->status, ['pending', 'failed'], true)) {
                return null;
            }

            DB::table('clinic_summary_deliveries')
                ->where(
                    'clinic_summary_delivery_id',
                    $delivery->clinic_summary_delivery_id
                )
                ->update([
                    'recipient_email' => $email,
                    'status' => 'processing',
                    'attempts' => $delivery->attempts + 1,
                    'processing_started_at' => now(),
                    'last_error' => null,
                    'updated_at' => now(),
                ]);

            return (int) $delivery->clinic_summary_delivery_id;
        }, 3);
    }

    private function hasValidLicense(object $clinic): bool
    {
        if ($clinic->saas_customer_id === null) {
            return true;
        }

        if (! $clinic->current_license_id) {
            return false;
        }

        $now = now();

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
            ->where('licenses.starts_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query
                    ->where('licenses.ends_at', '>', $now)
                    ->orWhere(function ($query) {
                        $query
                            ->where('plans.term_type', 'lifetime')
                            ->whereNull('licenses.ends_at');
                    });
            })
            ->exists();
    }

    private function toCents($amount): int
    {
        [$whole, $fraction] = array_pad(
            explode('.', (string) $amount, 2),
            2,
            ''
        );

        return ((int) $whole * 100)
            + (int) str_pad(
                substr($fraction, 0, 2),
                2,
                '0',
                STR_PAD_RIGHT
            );
    }

    private function decimal(int $cents): string
    {
        $prefix = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $prefix
            . intdiv($absolute, 100)
            . '.'
            . str_pad(
                (string) ($absolute % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }
}