<?php

namespace Tests\Feature;

use App\Models\User;

trait SeedOnce
{
    /**
     * Tracks whether the seeder has run in the current test method's transaction.
     * Reset by tearDownSeedOnce() between test methods.
     */
    private bool $seeded = false;

    protected function seedOnce(): void
    {
        if (!$this->seeded) {
            $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
            $this->seeded = true;
        }
    }

    protected function tearDownSeedOnce(): void
    {
        $this->seeded = false;
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
