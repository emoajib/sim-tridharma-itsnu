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
}
