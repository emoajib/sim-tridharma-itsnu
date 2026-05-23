<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $module = $request->query('module');
        $perPage = (int) $request->query('per_page', 50);

        $permissions = Permission::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($module, fn ($q) => $q->where('name', 'like', "{$module}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Get distinct modules (SQLite-compatible)
        $modules = Permission::query()
            ->selectRaw("DISTINCT SUBSTR(name, 1, INSTR(name, '.') - 1) as module")
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('module')
            ->pluck('module');

        return inertia('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'modules' => $modules,
            'filters' => compact('search', 'module'),
        ]);
    }
}
