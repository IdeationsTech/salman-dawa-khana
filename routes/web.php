<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NuskhaTemplateController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ClinicAccountController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\ClinicNotificationController;
use App\Http\Controllers\ClinicSecurityController;
use App\Http\Controllers\PatientIdSettingsController;
use App\Http\Controllers\NuskhaOrderController;
use App\Http\Controllers\NuskhaOrderRefundController;
use App\Http\Controllers\ClinicAuditController;

use App\Http\Controllers\PlatformAdmin\LoginController as PlatformAdminLoginController;
use App\Http\Controllers\PlatformAdmin\DashboardController as PlatformAdminDashboardController;
use App\Http\Controllers\PlatformAdmin\ClinicController as PlatformAdminClinicController;
use App\Http\Controllers\PlatformAdmin\ClinicEditController;
use App\Http\Controllers\PlatformAdmin\ClinicDetailsController;
use App\Http\Controllers\PlatformAdmin\ClinicCurrencyController;
use App\Http\Controllers\PlatformAdmin\SubscriptionPlanController;
use App\Http\Controllers\PlatformAdmin\LicensePaymentController;
use App\Http\Controllers\PlatformAdmin\ComplimentaryLicenseController;
use App\Http\Controllers\PlatformAdmin\LicenseController;
use App\Http\Controllers\PlatformAdmin\AuditLogController;
use App\Http\Controllers\PlatformAdmin\AccountController as PlatformAdminAccountController;

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureClinicAccess;
use App\Http\Middleware\EnsureClinicRole;
use App\Http\Middleware\ClinicAuditMiddleware;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

Route::view('/registration/pending', 'auth.pending')->name('registration.pending');

Route::get('/forgot-password', [PasswordResetController::class, 'request'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'email'])
    ->middleware('throttle:5,1')->name('password.email');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:5,1')->name('password.update');

Route::middleware([
    'auth',
    EnsureClinicAccess::class,
    ClinicAuditMiddleware::class,
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/notifications', [ClinicNotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [ClinicNotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::post('/notifications/{notification}/read', [ClinicNotificationController::class, 'markRead'])
        ->whereNumber('notification')->name('notifications.read');

    Route::resource('patients', PatientController::class);

    Route::middleware(EnsureClinicRole::class . ':Owner,Doctor')->group(function () {
        Route::resource('expenses', ExpenseController::class);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/orders/{order}/refund', [NuskhaOrderRefundController::class, 'create'])
            ->whereNumber('order')->name('orders.refunds.create');

        Route::post('/orders/{order}/refund', [NuskhaOrderRefundController::class, 'store'])
            ->whereNumber('order')->name('orders.refunds.store');
    });

    Route::get('/payments/bills/{bill}', [PaymentController::class, 'billReceipt'])
        ->name('payments.bills.show');

    Route::resource('payments', PaymentController::class);

    Route::resource('prescriptions/templates', NuskhaTemplateController::class)
        ->names('prescriptions.templates');

    Route::get('/prescriptions/patient-search', [PrescriptionController::class, 'searchPatients'])
        ->middleware('throttle:120,1')->name('prescriptions.patients.search');

    Route::get('/prescriptions/patients/{patient}/visits', [PrescriptionController::class, 'patientVisits'])
        ->whereNumber('patient')->middleware('throttle:120,1')
        ->name('prescriptions.patients.visits');

    Route::resource('prescriptions', PrescriptionController::class);

    Route::get('/orders', [NuskhaOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [NuskhaOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [NuskhaOrderController::class, 'store'])->name('orders.store');

    Route::get('/orders/{order}/edit', [NuskhaOrderController::class, 'edit'])
        ->whereNumber('order')->name('orders.edit');

    Route::put('/orders/{order}', [NuskhaOrderController::class, 'update'])
        ->whereNumber('order')->name('orders.update');

    Route::get('/orders/{order}', [NuskhaOrderController::class, 'show'])
        ->whereNumber('order')->name('orders.show');

    Route::post('/orders/{order}/payments', [NuskhaOrderController::class, 'receivePayment'])
        ->whereNumber('order')->name('orders.payments.store');

    Route::patch('/orders/{order}/status', [NuskhaOrderController::class, 'updateStatus'])
        ->whereNumber('order')->name('orders.status.update');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');

    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware(EnsureClinicRole::class . ':Owner')->name('settings.update');

    Route::get('/settings/account', [SettingController::class, 'account'])->name('settings.account');
    Route::put('/settings/account', [ClinicAccountController::class, 'update'])->name('settings.account.update');

    Route::get('/settings/payments', [SettingController::class, 'payments'])->name('settings.payments');

    Route::put('/settings/payments', [PaymentSettingsController::class, 'update'])
        ->middleware(EnsureClinicRole::class . ':Owner')->name('settings.payments.update');

    Route::get('/settings/notifications', [SettingController::class, 'notifications'])->name('settings.notifications');

    Route::put('/settings/notifications', [NotificationSettingsController::class, 'update'])
        ->middleware(EnsureClinicRole::class . ':Owner')->name('settings.notifications.update');

    Route::put('/settings/patient-id', [PatientIdSettingsController::class, 'update'])
        ->middleware(EnsureClinicRole::class . ':Owner')->name('settings.patient-id.update');

    Route::get('/settings/security', [SettingController::class, 'security'])->name('settings.security');

    Route::put('/settings/security/password', [ClinicSecurityController::class, 'updatePassword'])
        ->name('settings.security.password.update');

    Route::middleware(EnsureClinicRole::class . ':Owner')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/activity-history', [ClinicAuditController::class, 'index'])->name('audit.index');
    });

    Route::resource('visits', VisitController::class);
});

Route::prefix('platform-admin')->name('platform-admin.')->group(function () {
    Route::get('/login', [PlatformAdminLoginController::class, 'show'])->name('login');
    Route::post('/login', [PlatformAdminLoginController::class, 'login'])->name('login.submit');

    Route::middleware(EnsurePlatformAdmin::class)->group(function () {
        Route::get('/dashboard', [PlatformAdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/account', [PlatformAdminAccountController::class, 'index'])->name('account.index');
        Route::put('/account/profile', [PlatformAdminAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::put('/account/password', [PlatformAdminAccountController::class, 'updatePassword'])->name('account.password.update');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit-logs/{log}', [AuditLogController::class, 'show'])->whereNumber('log')->name('audit.show');

        Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
        Route::get('/plans', [SubscriptionPlanController::class, 'index'])->name('plans.index');
        Route::put('/plans/{plan}', [SubscriptionPlanController::class, 'update'])->whereNumber('plan')->name('plans.update');

        Route::get('/payments', [LicensePaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [LicensePaymentController::class, 'show'])->whereNumber('payment')->name('payments.show');
        Route::get('/payments/{payment}/receipt', [LicensePaymentController::class, 'receipt'])->whereNumber('payment')->name('payments.receipt');

        Route::get('/clinics', [PlatformAdminClinicController::class, 'index'])->name('clinics.index');
        Route::get('/clinics/{clinic}/edit', [ClinicEditController::class, 'edit'])->whereNumber('clinic')->name('clinics.edit');

        Route::get('/clinics/{clinic}/currency', [ClinicCurrencyController::class, 'edit'])->whereNumber('clinic')->name('clinics.currency.edit');
        Route::put('/clinics/{clinic}/currency', [ClinicCurrencyController::class, 'update'])->whereNumber('clinic')->name('clinics.currency.update');

        Route::put('/clinics/{clinic}/details', [ClinicDetailsController::class, 'update'])->whereNumber('clinic')->name('clinics.details.update');
        Route::post('/clinics/{clinic}/status', [ClinicEditController::class, 'changeStatus'])->whereNumber('clinic')->name('clinics.status');
        Route::post('/clinics/{clinic}/subscription', [ClinicEditController::class, 'changePlan'])->whereNumber('clinic')->name('clinics.subscription');

        Route::get('/clinics/{clinic}/complimentary', [ComplimentaryLicenseController::class, 'create'])->whereNumber('clinic')->name('clinics.complimentary.create');
        Route::post('/clinics/{clinic}/complimentary', [ComplimentaryLicenseController::class, 'store'])->whereNumber('clinic')->name('clinics.complimentary.store');

        Route::get('/clinics/{clinic}', [PlatformAdminClinicController::class, 'show'])->whereNumber('clinic')->name('clinics.show');
        Route::post('/clinics/{clinic}/approve', [PlatformAdminClinicController::class, 'approve'])->whereNumber('clinic')->name('clinics.approve');
        Route::post('/clinics/{clinic}/reject', [PlatformAdminClinicController::class, 'reject'])->whereNumber('clinic')->name('clinics.reject');

        Route::post('/logout', [PlatformAdminLoginController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Platform Admin Password Recovery
|--------------------------------------------------------------------------
*/

Route::prefix('platform-admin')
    ->name('platform-admin.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route(
                auth('platform_admin')->check()
                    ? 'platform-admin.dashboard'
                    : 'platform-admin.login'
            );
        })->name('home');

        Route::get('/forgot-password', [
            \App\Http\Controllers\PlatformAdmin\PasswordResetController::class,
            'request',
        ])->name('password.request');

        Route::post('/forgot-password', [
            \App\Http\Controllers\PlatformAdmin\PasswordResetController::class,
            'email',
        ])->middleware('throttle:5,1')
            ->name('password.email');

        Route::get('/reset-password/{token}', [
            \App\Http\Controllers\PlatformAdmin\PasswordResetController::class,
            'form',
        ])->name('password.reset');

        Route::post('/reset-password', [
            \App\Http\Controllers\PlatformAdmin\PasswordResetController::class,
            'reset',
        ])->middleware('throttle:5,1')
            ->name('password.update');
    });