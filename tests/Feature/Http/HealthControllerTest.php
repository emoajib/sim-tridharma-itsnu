<?php

namespace Tests\Feature\Http;

use App\Http\Controllers\HealthController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * No route is registered for HealthController.
     * Test the controller class directly.
     *
     * Note: In the test environment (SQLite), the database check uses
     * sqlite_version() which always works. Cache (Redis) and Queue
     * checks may fail since Redis might not be running, but the health
     * endpoint should still return structured data regardless.
     */
    public function test_health_check_returns_structured_json(): void
    {
        $controller = new HealthController();
        $response = $controller->__invoke();

        $this->assertNotNull($response);
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $data = $response->getData(true);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('app_env', $data);
        $this->assertArrayHasKey('app_debug', $data);
        $this->assertArrayHasKey('database', $data);
        $this->assertArrayHasKey('cache', $data);
        $this->assertArrayHasKey('queue', $data);
    }

    public function test_health_check_database_is_detected(): void
    {
        $controller = new HealthController();
        $response = $controller->__invoke();

        $data = $response->getData(true);

        // Database check should work with SQLite in test environment
        $this->assertArrayHasKey('status', $data['database']);
        $this->assertArrayHasKey('driver', $data['database']);
        $this->assertEquals('sqlite', $data['database']['driver']);
    }

    public function test_health_check_returns_json(): void
    {
        $controller = new HealthController();
        $response = $controller->__invoke();

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
