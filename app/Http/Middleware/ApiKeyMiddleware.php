<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Internal-Key');
        $expectedKey = config('ai-service.internal_key', 'default-internal-key');

        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json([
                'error' => 'Unauthorized - Invalid or missing API key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
