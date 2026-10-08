<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        Log::info('🔴 DIAGNOSTIC: /admin/roles HIT by user', [
            'email' => $user?->email,
            'roles' => $user?->getRoleNames()->toArray(),
            'has_admin_view_via_gate' => $user?->can('admin.view'),
            'is_super_admin_role' => $user?->hasRole('Super Admin'),
        ]);

        $search = $request->query('search');
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        $roles = Role::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->with('permissions')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $allPermissions = Cache::remember('all_permissions', 3600, fn () => 
            Permission::orderBy('name')->get(['id', 'name', 'guard_name'])->toArray()
        );

        return inertia('Admin/Roles/Index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
            'filters' => compact('search'),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        if (! empty($validated['permission_ids'])) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permission_ids'])->pluck('name'));
        }

        return back()->with('success', "Role '{$role->name}' berhasil dibuat.");
    }

    public function update(Request $request, Role $role)
    {
        $this->guardSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update(['name' => $validated['name']]);

        if (array_key_exists('permission_ids', $validated)) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permission_ids'])->pluck('name'));
        }

        return back()->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    public function syncPermissions(Request $request, Role $role)
    {
        // Roles & permissions ADALAH model otorisasi. Mengubahnya berarti
        // mengubah siapa yang boleh apa — jadi butuh privilege tertinggi.
        // Tanpa guard ini, admin biasa bisa memberi permission admin.* ke role
        // miliknya sendiri lalu escalate lewat role itu.
        $this->guardSuperAdmin($request);

        $validated = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionNames = Permission::whereIn('id', $validated['permission_ids'])->pluck('name');

        $role->syncPermissions($permissionNames);

        Log::warning('Role permissions changed', [
            'actor' => $request->user()?->email,
            'role' => $role->name,
            'permission_count' => $permissionNames->count(),
        ]);

        return response()->json([
            'success' => true,
            'permissions' => $role->permissions()->pluck('name')->toArray(),
        ]);
    }

    public function destroy(Request $request, Role $role)
    {
        $this->guardSuperAdmin($request);

        // Proteksi berbasis nama (bukan hanya 1 role): seluruh role
        // lintas-fakultas tidak boleh dihapus dari UI biasa.
        if (in_array($role->name, ['Super Admin', 'Rektor', 'WR 1 Akademik',
            'WR 2 Keuangan & Sarpras', 'WR 3 Kemahasiswaan', 'LPM'], true)) {
            return back()->with('error', 'Tidak dapat menghapus role lintas-fakultas.');
        }

        $name = $role->name;
        $role->delete();

        Log::warning('Role deleted', [
            'actor' => $request->user()?->email,
            'role' => $name,
        ]);

        return back()->with('success', "Role '{$name}' berhasil dihapus.");
    }

    /**
     * Manage role & permission hanya untuk Super Admin.
     */
    private function guardSuperAdmin(Request $request): void
    {
        if (! $request->user()?->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat mengelola role dan permission.');
        }
    }
}
