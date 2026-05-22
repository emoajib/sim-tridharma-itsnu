<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SystemRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:refresh {--seed-users : Whether to seed complete blueprint users}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automate "Triple Refresh" (Migrate, Seed Permissions, Clear Cache) to prevent login errors.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting System Refresh (Stability Protocol)...');

        // 1. Migrate
        $this->info('1/4 Running migrations...');
        Artisan::call('migrate', [], $this->getOutput());

        // 2. Seed Permissions
        $this->info('2/4 Seeding roles & permissions...');
        Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder'], $this->getOutput());

        // 3. Optional: Seed Complete Users
        if ($this->option('seed-users')) {
            $this->info('3/4 Seeding complete blueprint users...');
            Artisan::call('db:seed', ['--class' => 'CompleteUserSeeder'], $this->getOutput());
        } else {
            $this->info('3/4 Skipping user seeding (use --seed-users to include).');
        }

        // 4. Clear Cache
        $this->info('4/4 Clearing all caches...');
        Artisan::call('optimize:clear', [], $this->getOutput());

        $this->info('✅ System is now stable and up-to-date.');
    }
}
