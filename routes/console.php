<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('commerce:check-overdue-services')->dailyAt('01:00');
Schedule::command('commerce:check-termination-services')->dailyAt('02:00');
