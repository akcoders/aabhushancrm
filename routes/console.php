<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:daily-notifications')->dailyAt('08:00')->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('retention:scan')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('smart-tasks:generate')->dailyAt('09:05')->withoutOverlapping();
Schedule::command('smart-tasks:generate')->everyTwoHours()->withoutOverlapping();
