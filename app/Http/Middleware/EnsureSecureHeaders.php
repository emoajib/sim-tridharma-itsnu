<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $viteDev = app()->environment('local') ? ' http://127.0.0.1:5173' : '';
        $fontCdn = 'https://fonts.bunny.net';
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'{$viteDev}";
        $styleSrc = "'self' 'unsafe-inline'{$viteDev} {$fontCdn}";
        $fontSrc = "'self'{$viteDev} {$fontCdn}";
        $connectSrc = "'self' wss://{$request->getHost()}{$viteDev} ws://127.0.0.1:5173";

        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src {$scriptSrc}; style-src {$styleSrc}; img-src 'self' data:; font-src {$fontSrc}; connect-src {$connectSrc}; form-action 'self'");

        return $response;
    }
}
