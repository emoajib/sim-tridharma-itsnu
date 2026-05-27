<?php

declare(strict_types=1);

use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Middleware\EnsureSecureHeaders;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\SetActiveRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',

        // ── Routes outside any middleware group (no auth/csrf) ──────────
        then: function () {
            Route::get('/health', \App\Http\Controllers\HealthController::class);
        },
    )

    // ── Event Discovery ───────────────────────────────────────────────
    // Laravel auto-discovers events by scanning listener directories.
    // For PRODUCTION performance, run:
    //   php artisan event:cache
    // This bakes all event/listener mappings into a single cached file.
    // Remember to re-run after adding/removing events or listeners.
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SetActiveRole::class,
        ]);

        $middleware->api(append: [
            PermissionMiddleware::class,
        ]);

        $middleware->append(HandleCors::class);
        $middleware->append(EnsureSecureHeaders::class);

        $middleware->alias([
            'api.key' => ApiKeyMiddleware::class,
            'audit' => \App\Http\Middleware\LogSecurityAudit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->expectsJson() || $request->is('api/*');
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Resource not found'], 404);
            }

            return inertia('Errors/NotFound')->toResponse($request)->setStatusCode(404);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $e->getMessage() ?: 'Error'], $e->getStatusCode());
            }

            $page = match ($e->getStatusCode()) {
                403 => 'Errors/Forbidden',
                404 => 'Errors/NotFound',
                429 => 'Errors/TooManyRequests',
                default => 'Errors/ServerError',
            };

            return inertia($page)->toResponse($request)->setStatusCode($e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof ValidationException) {
                return null;
            }

            Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                $message = app()->environment('local') ? $e->getMessage() : 'Internal server error';

                return response()->json(['message' => $message], 500);
            }

            if (app()->environment('local')) {
                throw $e;
            }

            return inertia('Errors/ServerError')->toResponse($request)->setStatusCode(500);
        });
    })->create();
