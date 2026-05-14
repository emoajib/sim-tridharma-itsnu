<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('agents:run-all')->hourly()->appendOutputTo(storage_path('logs/agents.log'));
Schedule::command('agents:run-all --agent=integrasi')->everySixHours()->appendOutputTo(storage_path('logs/integrasi.log'));
