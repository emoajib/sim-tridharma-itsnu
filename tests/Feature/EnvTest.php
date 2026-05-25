<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvTest extends TestCase
{
    public function test_env_values(): void
    {
        dump("APP_ENV: " . config('app.env'));
        dump("DB_CONNECTION: " . config('database.default'));
        dump("DB_DATABASE: " . config('database.connections.pgsql.database'));
        dump("SESSION_DRIVER: " . config('session.driver'));
        
        $this->assertEquals('testing', config('app.env'));
        $this->assertEquals('sim_tridharma_itsnu_test', config('database.connections.pgsql.database'));
    }
}
