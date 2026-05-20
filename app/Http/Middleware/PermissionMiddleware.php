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
            
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }

    private function extractPermission(string $routeName): ?string
    {
        $segments = explode('.', $routeName);
        
        // Handle single segment route names (e.g., 'prediksi', 'dashboard')
        if (count($segments) === 1) {
            $module = $segments[0];
            
            // Map AI agents to agent-ai module
            if (in_array($module, ['prediksi', 'peringatan', 'verifikasi', 'generator', 'rekomendasi', 'integrasi'])) {
                return "agent-ai.view";
            }
            
            // Other single segment routes that don't need permission enforcement
            if (in_array($module, ['dashboard', 'welcome', 'profile'])) {
                return null;
            }
            
            return "{$module}.view";
        }

        $module = $segments[0];
        $action = end($segments);

        // Map AI agent sub-routes to agent-ai module
        if (in_array($module, ['prediksi', 'peringatan', 'verifikasi', 'generator', 'rekomendasi', 'integrasi'])) {
            $module = 'agent-ai';
        }

        // Routes that don't need specific permission
        if (in_array($module, ['role'])) {
            return null;
        }

        // Map import routes to portofolio module
        if ($module === 'import') {
            return "portofolio.create";
        }

        // Map dokumen actions to upload
        if ($module === 'dokumen' && in_array($action, ['store', 'create'])) {
            return "dokumen.upload";
        }

        // Map ai-resolve action to agent-ai module
        if ($action === 'ai-resolve') {
            return "agent-ai.trigger";
        }

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
            'sync' => 'trigger',
            'ai-resolve' => 'trigger',
        ];

        if (isset($actionMap[$action])) {
            return "{$module}.{$actionMap[$action]}";
        }

        return "{$module}.view";
    }
}
