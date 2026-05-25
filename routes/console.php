<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('agents:run-all')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/agents.log'));

Schedule::command('reconcile:auto --hours=24')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reconcile.log'));

Schedule::job(new \App\Jobs\CheckCapaDeadline())
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/capa-deadline.log'));
