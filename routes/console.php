<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Daily Clinic Summary — Previous Day, UAE Time
|--------------------------------------------------------------------------
*/

\Illuminate\Support\Facades\Schedule::command(
    'clinics:daily-summary --send'
)
    ->dailyAt('00:10')
    ->timezone('Asia/Dubai')
    ->withoutOverlapping(60); 