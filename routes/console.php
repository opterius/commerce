<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('commerce:generate-renewal-invoices')->dailyAt('00:00');
Schedule::command('commerce:mark-overdue-invoices')->dailyAt('00:30');
Schedule::command('commerce:check-overdue-services')->dailyAt('01:00');
Schedule::command('commerce:check-termination-services')->dailyAt('02:00');
Schedule::command('commerce:auto-close-tickets')->dailyAt('03:00');
Schedule::command('commerce:check-expiring-domains')->dailyAt('04:00');
Schedule::command('commerce:sync-domain-statuses')->dailyAt('05:00');
