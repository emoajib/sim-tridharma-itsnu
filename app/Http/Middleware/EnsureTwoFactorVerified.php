<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * If the user has 2FA enabled but hasn't verified it in this session,
     * redirect them to the 2FA challenge page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled && !$request->session()->get('2fa_verified')) {
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
