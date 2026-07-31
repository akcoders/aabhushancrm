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

Artisan::command('marketing:dispatch-scheduled', function () {
    $service = app(\App\Services\MarketingCampaignService::class);
    \App\Models\MarketingCampaign::where('status', 'Scheduled')->where('scheduled_at', '<=', now())
        ->each(fn ($campaign) => $service->launch($campaign));
})->purpose('Dispatch due CRM marketing campaigns');

Schedule::command('marketing:dispatch-scheduled')->everyMinute()->withoutOverlapping();
