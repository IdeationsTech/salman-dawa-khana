<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NuskhaTemplateController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\PlatformAdmin\LoginController as PlatformAdminLoginController;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Controllers\PlatformAdmin\DashboardController as PlatformAdminDashboardController;
use App\Http\Controllers\PlatformAdmin\ClinicController as PlatformAdminClinicController;


/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [
    LoginController::class,
    'show',
])->name('login');

Route::post('/login', [
    LoginController::class,
    'login',
])->name('login.submit');

Route::post('/logout', [
    LoginController::class,
    'logout',
])->name('logout');

Route::get('/register', [
    RegisterController::class,
    'show',
])->name('register');

Route::post('/register', [
    RegisterController::class,
    'register',
])->name('register.submit');

/*
|--------------------------------------------------------------------------
| Protected Application Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index',
    ])->name('dashboard');

    Route::resource(
        'patients',
        PatientController::class
    );

    Route::resource(
        'expenses',
        ExpenseController::class
    );

    /*
|--------------------------------------------------------------------------
| Payments and Bill Receipt
|--------------------------------------------------------------------------
*/

Route::get('/payments/bills/{bill}', [
    PaymentController::class,
    'billReceipt',
])->name('payments.bills.show');

Route::resource('payments', PaymentController::class);

    /*
    |----------------------------------------------------------------------
    | Nuskha Template Routes
    | Must come before prescriptions/{prescription}
    |----------------------------------------------------------------------
    */

    Route::resource(
        'prescriptions/templates',
        NuskhaTemplateController::class
    )->names('prescriptions.templates');

    Route::resource(
        'prescriptions',
        PrescriptionController::class
    );

    Route::get('/reports', [
        ReportController::class,
        'index',
    ])->name('reports.index');

    Route::get('/settings', [
        SettingController::class,
        'index',
    ])->name('settings.index');

    Route::get('/settings/account', [
        SettingController::class,
        'account',
    ])->name('settings.account');

    Route::get('/settings/payments', [
        SettingController::class,
        'payments',
    ])->name('settings.payments');

    Route::get('/settings/notifications', [
        SettingController::class,
        'notifications',
    ])->name('settings.notifications');

    Route::get('/settings/security', [
        SettingController::class,
        'security',
    ])->name('settings.security');

    Route::put('/settings', [
        SettingController::class,
        'update',
    ])->name('settings.update');

    Route::resource(
        'users',
        UserController::class
    );

    Route::resource(
        'visits',
        VisitController::class
    );
});


/*
|--------------------------------------------------------------------------
| Platform Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('platform-admin')
    ->name('platform-admin.')
    ->group(function () {

        Route::get('/login', [
            PlatformAdminLoginController::class,
            'show',
        ])->name('login');

        Route::post('/login', [
            PlatformAdminLoginController::class,
            'login',
        ])->name('login.submit');

        Route::middleware(EnsurePlatformAdmin::class)
            ->group(function () {

                Route::get('/dashboard', [
                    PlatformAdminDashboardController::class,
                    'index',
                ])->name('dashboard');

                Route::get('/clinics', [
                PlatformAdminClinicController::class,
                    'index',
                ])->name('clinics.index');

                Route::get('/clinics/{clinic}', [
                    PlatformAdminClinicController::class,
                    'show',
                ])->whereNumber('clinic')->name('clinics.show');

                Route::post('/clinics/{clinic}/approve', [
                    PlatformAdminClinicController::class,
                    'approve',
                ])->whereNumber('clinic')->name('clinics.approve');

                Route::post('/clinics/{clinic}/reject', [
                    PlatformAdminClinicController::class,
                    'reject',
                ])->whereNumber('clinic')->name('clinics.reject');

                Route::post('/logout', [
                    PlatformAdminLoginController::class,
                    'logout',
                ])->name('logout');

            });

    });

    Route::view('/registration/pending', 'auth.pending')
    ->name('registration.pending');