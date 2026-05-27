<?php

namespace Tests\Feature;

use App\Models\User;

trait SeedOnce
{
    /**
     * Tracks whether the seeder has run in the current test method's transaction.
     * Reset by tearDownSeedOnce() between test methods.
     */
    protected static bool $seeded = false;

    /**
     * Seed role/permission data once per test method.
     * Safe to call multiple times within the same test — only runs once.
     */
    protected function seedOnce(): void
    {
        if (!static::$seeded) {
            $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
            static::$seeded = true;
        }
    }

    /**
     * Automatically discovered by Laravel's setUpTraits() lifecycle.
     * Resets $seeded after each test method so the next test seeds again
     * (required because RefreshDatabase rolls back the transaction).
     */
    protected function tearDownSeedOnce(): void
    {
        static::$seeded = false;
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@itsnu.ac.id')->firstOrFail();
    }

    protected function kaprodi(): User
    {
        return User::where('email', 'kaprodi@itsnu.ac.id')->firstOrFail();
    }

    protected function dosen(): User
    {
        return User::where('email', 'dosen@itsnu.ac.id')->firstOrFail();
    }
}
