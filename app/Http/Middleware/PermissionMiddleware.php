<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    private const AI_AGENT_MODULES = [
        'prediksi', 'peringatan', 'verifikasi', 'generator', 'rekomendasi', 'integrasi',
    ];

    private const MODULE_MAP = [
        'import' => 'data-import',
        'data-import' => 'data-import',
        'reconciliation' => 'reconciliation',
        'rkat' => 'rkat',
        'iku' => 'iku',
        'cascading' => 'cascading',
    ];

    private const ACTION_MAP = [
        'index' => 'view', 'show' => 'view',
        'create' => 'create', 'store' => 'create',
        'edit' => 'edit', 'update' => 'edit',
        'destroy' => 'delete', 'remove' => 'delete',
        'run' => 'trigger', 'generate' => 'generate',
        'download' => 'view', 'upload' => 'upload',
        'switch' => 'view', 'status' => 'view',
        'latest' => 'view', 'ask' => 'view',
        'reindex' => 'trigger', 'sync' => 'trigger',
        'ai-resolve' => 'trigger', 'test' => 'view',
        'import-preview' => 'view', 'chunks' => 'view',
        'approve' => 'approve', 'reject' => 'reject',
        'toggle' => 'edit', 'markRead' => 'edit',
        'markAllRead' => 'edit', 'kuisioner' => 'view',
        'jawaban' => 'view',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        Log::debug('=== PERMISSION MIDDLEWARE EXECUTING ===');
        Log::debug('PermissionMiddleware: START - Handling request for '.($request->route()?->getName() ?? 'no route'));

        if (! $request->user()) {
            Log::debug('PermissionMiddleware: No user, passing through');

            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (! $routeName) {
            Log::debug('PermissionMiddleware: No route name, passing through');

            return $next($request);
        }

        $permission = $this->extractPermission($routeName);

        // Debug logging
        Log::debug('PermissionMiddleware: Checking route '.$routeName.', permission: '.$permission);
        Log::debug('User permissions: '.print_r($request->user()->getPermissionNames()->toArray(), true));

        if ($permission && ! $request->user()->can($permission)) {
            Log::debug('PermissionMiddleware: Permission denied for '.$permission);
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'required_permission' => $permission,
                ], 403);
            }

            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        Log::debug('PermissionMiddleware: Permission granted, passing through');

        return $next($request);
    }

    private function extractPermission(string $routeName): ?string
    {
        $segments = explode('.', $routeName);

        if (count($segments) === 1) {
            return $this->handleSingleSegment($segments[0]);
        }

        // Check for two-segment modules first (e.g., admin.users, admin.roles)
        if (count($segments) >= 2) {
            $twoSegment = $segments[0].'.'.$segments[1];
            if (isset(self::MODULE_MAP[$twoSegment])) {
                $action = end($segments);
                $mappedAction = self::ACTION_MAP[$action] ?? 'view';
                return self::MODULE_MAP[$twoSegment].'.'.$mappedAction;
            }
        }

        $module = $segments[0];
        $action = end($segments);

        if (in_array($module, self::AI_AGENT_MODULES, true)) {
            $module = 'agent-ai';
        }

        $module = self::MODULE_MAP[$module] ?? $module;

        if (in_array($module, ['role', 'profile'], true)) {
            return null;
        }

        $mappedAction = self::ACTION_MAP[$action] ?? 'view';

        return "{$module}.{$mappedAction}";
    }

    private function handleSingleSegment(string $segment): ?string
    {
        if (in_array($segment, self::AI_AGENT_MODULES, true)) {
            return 'agent-ai.view';
        }

        if (in_array($segment, ['dashboard', 'welcome', 'rag'], true)) {
            return null;
        }

        return "{$segment}.view";
    }
}
