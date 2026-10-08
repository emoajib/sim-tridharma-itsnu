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
        $expectedKey = config('ai-service.internal_key');

        // Fail-closed: tanpa key yang dikonfigurasi, janganFall back ke
        // literal hardcoded. Fallback dulu ('default-internal-key') berarti
        // salah-deploy = endpoint terbuka dengan kunci yang ada di source code.
        if (empty($expectedKey)) {
            return response()->json([
                'error' => 'Server misconfigured: internal key not set',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // constant-time: hindari timing side-channel pada perbandingan key
        if (! $apiKey || ! hash_equals($expectedKey, $apiKey)) {
            return response()->json([
                'error' => 'Unauthorized - Invalid or missing API key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
