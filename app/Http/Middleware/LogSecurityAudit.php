<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Security\SecurityAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSecurityAudit
{
    public function __construct(
        private readonly SecurityAuditService $securityAuditService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $action = $request->route()?->getAction('audit') ?? $request->route()?->action['audit'] ?? null;

        if ($action !== null) {
            $this->securityAuditService->log(
                action: $action,
                description: sprintf('%s %s', $request->method(), $request->path()),
            );
        }

        return $response;
    }
}
