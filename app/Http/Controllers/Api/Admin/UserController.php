<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $perPage = (int) $request->query('per_page', 15);

        $users = User::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($role, fn ($q) => $q->whereHas('roles', fn ($rq) => $rq->where('name', $role)))
            ->with('roles')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Admin/Users/Index', [
            'users' => $users,
            'filters' => compact('search', 'role'),
            'roles' => Role::orderBy('name')->get(['id', 'name', 'guard_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'dosen_id' => ['nullable', 'integer', 'exists:m_dosen,id'],
            'prodi_id' => ['nullable', 'integer', 'exists:m_prodi,id'],
        ]);

        // Cross-validation for role requirements
        $this->validateRoleRequirements($request, $validated['role_ids'] ?? []);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $roleIds = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $user = User::create($validated);
        if (! empty($roleIds)) {
            $user->syncRoles(Role::whereIn('id', $roleIds)->pluck('name'));
        }

        return back()->with('success', "User '{$user->name}' berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'dosen_id' => ['nullable', 'integer', 'exists:m_dosen,id'],
            'prodi_id' => ['nullable', 'integer', 'exists:m_prodi,id'],
        ]);

        // Cross-validation for role requirements
        $this->validateRoleRequirements($request, $validated['role_ids'] ?? []);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $roleIds = $validated['role_ids'] ?? null;
        unset($validated['role_ids']);

        $user->update($validated);

        if ($roleIds !== null) {
            $user->syncRoles(Role::whereIn('id', $roleIds)->pluck('name'));
        }

        return back()->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    private function validateRoleRequirements(Request $request, array $roleIds): void
    {
        if (empty($roleIds)) {
            return;
        }

        $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        $errors = [];

        if (in_array('Dosen', $roles) && ! $request->dosen_id) {
            $errors['dosen_id'] = 'Role Dosen wajib menyertakan data Dosen.';
        }

        if ((in_array('Kaprodi', $roles) || in_array('Dekan', $roles) || in_array('Staf Prodi', $roles)) && ! $request->prodi_id) {
            $errors['prodi_id'] = 'Role Kaprodi/Dekan/Staf Prodi wajib menyertakan data Program Studi.';
        }

        if (! empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function audit()
    {
        $users = User::with('roles')->get();
        $issues = [];

        foreach ($users as $user) {
            $roles = $user->getRoleNames()->toArray();
            
            if (in_array('Dosen', $roles) && ! $user->dosen_id) {
                $issues[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'Dosen',
                    'issue' => 'Profil Dosen belum tertaut.',
                ];
            }

            if (array_intersect(['Kaprodi', 'Dekan', 'Staf Prodi'], $roles) && ! $user->prodi_id) {
                $issues[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => implode(', ', array_intersect(['Kaprodi', 'Dekan', 'Staf Prodi'], $roles)),
                    'issue' => 'Data Program Studi belum tertaut.',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'total_users' => count($users),
            'issue_count' => count($issues),
            'issues' => $issues,
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->email === 'admin@itsnu.ac.id') {
            return back()->with('error', 'Tidak dapat menghapus Super Admin utama.');
        }

        $user->delete();

        return back()->with('success', "User '{$user->name}' berhasil dihapus.");
    }

    public function syncRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->syncRoles(Role::whereIn('id', $validated['role_ids'])->pluck('name'));

        return response()->json([
            'success' => true,
            'roles' => $user->getRoleNames()->toArray(),
        ]);
    }
}
