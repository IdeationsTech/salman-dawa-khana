<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/', function () {
    return redirect()->route('login');
});

// -- Registration form --

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// -- Dashboard --


Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');


// -- Patients --


Route::get('/patients', function () {
    return view('patients.index');
})->name('patients.index');


// -- Patients create --


Route::get('/patients/create', function () {
    return view('patients.create');
})->name('patients.create');


// -- Patients profile --


Route::get('/patients/{id}', function ($id) {
    return view('patients.show');
})->name('patients.show');




// -- Patients Visits --

Route::get('/visits', function () {
    return view('visits.index');
})->name('visits.index');



// -- Prescriptions --

Route::get('/prescriptions', function () {
    return view('prescriptions.index');
})->name('prescriptions.index');


// -- Prescriptions Templates--


Route::get('/prescriptions/templates', function () {
    return view('prescriptions.templates.index');
})->name('prescriptions.templates');


// -- Prescriptions Templates Create--


Route::get('/prescriptions/templates/create', function () {
    return view('prescriptions.templates.create');
})->name('prescriptions.templates.create');


// -- Prescriptions Create--


Route::get('/prescriptions/create', function () {
    return view('prescriptions.create');
})->name('prescriptions.create');


// -- Prescriptions Show--


Route::get('/prescriptions/{id}', function ($id) {
    return view('prescriptions.show');
})->name('prescriptions.show');


// -- Payments--


Route::get('/payments', function () {
    return view('payments.index');
})->name('payments.index');


// -- Payments Create--

Route::get('/payments/create', function () {
    return view('payments.create');
})->name('payments.create');


// -- Payments show--

Route::get('/payments/{id}', function ($id) {
    return view('payments.show');
})->name('payments.show');


// -- Expenses--


Route::get('/expenses', function () {
    return view('expenses.index');
})->name('expenses.index');


// -- Expenses Create--


Route::get('/expenses/create', function () {
    return view('expenses.create');
})->name('expenses.create');


// -- Expenses Show--


Route::get('/expenses/{id}', function ($id) {
    return view('expenses.show');
})->name('expenses.show');


// -- Reports--


Route::get('/reports', function () {
    return view('reports.index');
})->name('reports.index');


// -- Settings--


Route::get('/settings', function () {
    return view('settings.index');
})->name('settings.index');


// -- Settings Accounts--


Route::get('/settings/account', function () {
    return view('settings.account');
})->name('settings.account');


// -- Settings Payments--


Route::get('/settings/payments', function () {
    return view('settings.payments');
})->name('settings.payments');


// -- Settings notification--


Route::get('/settings/notifications', function () {
    return view('settings.notifications');
})->name('settings.notifications');


// -- Settings security--


Route::get('/settings/security', function () {
    return view('settings.security');
})->name('settings.security');


// -- Users--


Route::get('/users', function () {
    return view('users.index');
})->name('users.index');


// -- Users Create--


Route::get('/users/create', function () {
    return view('users.create');
})->name('users.create');


// -- Users Create--


Route::get('/visits/create', function () {
    return view('visits.create');
})->name('visits.create');


// -- Users Create--

