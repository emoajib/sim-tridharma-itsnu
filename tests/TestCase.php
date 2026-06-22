<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure a clean database file before the first test in the process.
     */
    protected static bool $databaseCleaned = false;

    protected function setUp(): void
    {
        if (!static::$databaseCleaned) {
            $databasePath = getcwd() . '/database/database-test.sqlite';
            if (file_exists($databasePath)) {
                unlink($databasePath);
            }
            touch($databasePath);
            static::$databaseCleaned = true;
        }

        parent::setUp();
    }

    /**
     * Run the given seeder after RefreshDatabase has migrated the database.
     * This avoids running the seeder per test method.
     */
    protected function afterRefreshingDatabase()
    {
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }
}
