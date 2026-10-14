<?php

namespace App\Providers;

use App\Services\ClinicAuditService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            $path = route('password.reset', ['token' => $token], false);

            return rtrim(config('app.url'), '/')
                . $path
                . '?'
                . http_build_query([
                    'email' => $user->getEmailForPasswordReset(),
                ]);
        });

        $models = [
            \App\Models\Clinic::class,
            \App\Models\User::class,
            \App\Models\Patient::class,
            \App\Models\Visit::class,
            \App\Models\Prescription::class,
            \App\Models\PrescriptionItem::class,
            \App\Models\NuskhaTemplate::class,
            \App\Models\NuskhaItem::class,
            \App\Models\Bill::class,
            \App\Models\BillItem::class,
            \App\Models\Payment::class,
            \App\Models\Expense::class,
            \App\Models\Setting::class,
            \App\Models\NuskhaOrder::class,
            \App\Models\NuskhaOrderItem::class,
            \App\Models\NuskhaOrderPayment::class,
        ];

        foreach ($models as $modelClass) {
            $modelClass::created(fn ($model) =>
                app(ClinicAuditService::class)->capture($model, 'created'));

            $modelClass::updated(fn ($model) =>
                app(ClinicAuditService::class)->capture($model, 'updated'));

            $modelClass::deleted(fn ($model) =>
                app(ClinicAuditService::class)->capture($model, 'deleted'));
        }
    }
}