<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvTest extends TestCase
{
    public function test_env(): void
    {
        dump('APP_ENV: '.config('app.env'));
        dump('DB_CONNECTION: '.config('database.default'));
        dump('SESSION_DRIVER: '.config('session.driver'));
        $this->assertTrue(true);
    }
}
