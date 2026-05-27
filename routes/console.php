<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ===== AI AGENTS =====
Schedule::command('agents:run-all')
    ->everyFourHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/agents.log'));

// ===== DATA INTEGRATION =====
Schedule::command('reconcile:auto --hours=24')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reconcile.log'));

// ===== SPMI =====
Schedule::job(new \App\Jobs\CheckCapaDeadline())
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/capa-deadline.log'));

// ===== MAINTENANCE =====
Schedule::command('sanctum:prune-expired --hours=24')
    ->dailyAt('03:00')
    ->appendOutputTo(storage_path('logs/maintenance.log'));

Schedule::command('model:prune')
    ->dailyAt('03:30')
    ->appendOutputTo(storage_path('logs/maintenance.log'));

Schedule::command('queue:prune-batches --hours=48 --unfinished=72')
    ->dailyAt('04:00')
    ->appendOutputTo(storage_path('logs/maintenance.log'));

Schedule::command('queue:flush')
    ->dailyAt('04:30')
    ->appendOutputTo(storage_path('logs/maintenance.log'));

// ===== BACKUP =====
Schedule::command('db:backup --no-upload')
    ->dailyAt('01:00')
    ->appendOutputTo(storage_path('logs/backup.log'));

// ===== CACHE =====
Schedule::command('cache:prune-stale-tags')
    ->hourly()
    ->appendOutputTo(storage_path('logs/maintenance.log'));
