<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    /**
     * Comprehensive health check for the application.
     * Checks database, cache (Redis), and queue connectivity.
     * Returns 200 if all OK, 503 if any component fails.
     */
    public function __invoke(): JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'app_env' => app()->environment(),
            'app_debug' => app()->hasDebugModeEnabled(),
        ];
        $httpStatus = 200;

        // ── Database Check ──────────────────────────────────────────────
        try {
            $pdo = DB::connection()->getPdo();
            $driver = config('database.default');
            $dbName = match ($driver) {
                'pgsql' => $pdo->query('SELECT current_database()')->fetchColumn(),
                'mysql' => $pdo->query('SELECT DATABASE()')->fetchColumn(),
                default => $pdo->query('SELECT sqlite_version()')->fetchColumn(),
            };
            $status['database'] = [
                'status' => 'connected',
                'name' => $dbName,
                'driver' => $driver,
            ];
        } catch (\Throwable $e) {
            $status['database'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            Log::warning('Healthcheck: Database connection failed', ['error' => $e->getMessage()]);
            $httpStatus = 503;
        }

        // ── Cache (Redis) Check ─────────────────────────────────────────
        try {
            Cache::store('redis')->get('healthcheck');
            $status['cache'] = [
                'status' => 'connected',
                'driver' => 'redis',
            ];
        } catch (\Throwable $e) {
            $status['cache'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            Log::warning('Healthcheck: Redis cache connection failed', ['error' => $e->getMessage()]);
            $httpStatus = 503;
        }

        // ── Queue Check ─────────────────────────────────────────────────
        try {
            $queueDriver = config('queue.default');
            // Attempt to get queue size or just check connection
            $status['queue'] = [
                'status' => 'connected',
                'driver' => $queueDriver,
            ];
            // For Redis driver, attempt a connection check
            if ($queueDriver === 'redis') {
                Queue::size();
            }
        } catch (\Throwable $e) {
            $status['queue'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
            Log::warning('Healthcheck: Queue connection failed', ['error' => $e->getMessage()]);
            $httpStatus = 503;
        }

        return response()->json($status, $httpStatus);
    }
}
