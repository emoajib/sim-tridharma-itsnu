<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        
        if (!$routeName) {
            return $next($request);
        }

        $permission = $this->extractPermission($routeName);
        
        if ($permission && !$request->user()->can($permission)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'required_permission' => $permission
                ], 403);
            }
            
            return response()->view('errors.403', [
                'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.'
            ], 403);
        }

        return $next($request);
    }

    private function extractPermission(string $routeName): ?string
    {
        $segments = explode('.', $routeName);
        
        if (count($segments) < 2) {
            return null;
        }

        $action = end($segments);
        $module = $segments[0];

        $actionMap = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            'run' => 'trigger',
            'generate' => 'generate',
            'download' => 'view',
            'upload' => 'upload',
            'remove' => 'delete',
            'switch' => 'view',
            'status' => 'view',
            'latest' => 'view',
            'ask' => 'view',
            'reindex' => 'trigger',
        ];

        if (isset($actionMap[$action])) {
            return "{$module}.{$actionMap[$action]}";
        }

        return "{$module}.view";
    }
}