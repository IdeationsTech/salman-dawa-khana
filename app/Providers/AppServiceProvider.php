<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Clinic;
use App\Models\Expense;
use App\Models\NuskhaItem;
use App\Models\NuskhaOrder;
use App\Models\NuskhaOrderItem;
use App\Models\NuskhaOrderPayment;
use App\Models\NuskhaTemplate;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionNuskha;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visit;
use App\Services\ClinicAuditService;
use App\Support\ClinicPermissions;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        Blade::if('clinicCan', fn (string $route) => ClinicPermissions::allows(auth()->user(), $route));

        ResetPassword::createUrlUsing(function ($user, string $token) {
            $path = route('password.reset', ['token' => $token], false);

            return rtrim(config('app.url'), '/')
                .$path
                .'?'
                .http_build_query([
                    'email' => $user->getEmailForPasswordReset(),
                ]);
        });

        $models = [
            Clinic::class,
            User::class,
            Patient::class,
            Visit::class,
            Prescription::class,
            PrescriptionItem::class,
            PrescriptionNuskha::class,
            NuskhaTemplate::class,
            NuskhaItem::class,
            Bill::class,
            BillItem::class,
            Payment::class,
            Expense::class,
            Setting::class,
            NuskhaOrder::class,
            NuskhaOrderItem::class,
            NuskhaOrderPayment::class,
        ];

        foreach ($models as $modelClass) {
            $modelClass::created(fn ($model) => app(ClinicAuditService::class)->capture($model, 'created'));

            $modelClass::updated(fn ($model) => app(ClinicAuditService::class)->capture($model, 'updated'));

            $modelClass::deleted(fn ($model) => app(ClinicAuditService::class)->capture($model, 'deleted'));
        }
    }
}
