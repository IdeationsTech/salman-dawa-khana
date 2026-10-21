<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Daily Clinic Summary — Previous Day, UAE Time
|--------------------------------------------------------------------------
*/

Schedule::command(
    'clinics:daily-summary --send --due-only'
)
    ->everyMinute()
    ->withoutOverlapping(60);
Schedule::command('clinic:backup --prune')->dailyAt('02:00')->timezone('UTC')->withoutOverlapping(120);
