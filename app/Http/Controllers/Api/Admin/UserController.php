<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SisterDosenUserImport;
use App\Imports\UserImport;
use App\Models\User;
use App\Services\Template\DataTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected DataTemplateService $templateService
    ) {}
    public function index(Request $request)
    {
        $user = $request->user();
        Log::info('🔴 DIAGNOSTIC: /admin/users HIT by user', [
            'email' => $user?->email,
            'roles' => $user?->getRoleNames()->toArray(),
            'has_admin_view_via_gate' => $user?->can('admin.view'),
            'is_super_admin_role' => $user?->hasRole('Super Admin'),
        ]);

        $search = $request->query('search');
        $role = $request->query('role');
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $users = User::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($role, fn ($q) => $q->whereHas('roles', fn ($rq) => $rq->where('name', $role)))
            ->with(['roles', 'dosen:id,nidn,nama_depan,nama_belakang,prodi_id', 'prodi:id,nama_prodi'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Admin/Users/Index', [
            'users' => $users,
            'filters' => compact('search', 'role'),
            'roles' => Cache::remember('roles_list', 3600, fn () => Role::orderBy('name')->get(['id', 'name', 'guard_name'])->toArray()),
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

    public function downloadTemplate()
    {
        return $this->templateService->download('users');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $filename = strtolower($file->getClientOriginalName());

        // Deteksi otomatis: jika nama file mengandung "dosen" atau "sister" → pakai importer khusus
        if (str_contains($filename, 'dosen') || str_contains($filename, 'sister')) {
            Excel::import(new SisterDosenUserImport, $file);
            return back()->with('success', 'Data dosen dari SISTER berhasil diimpor (menggunakan SisterDosenUserImport).');
        }

        // Default: pakai importer generic (untuk template manual)
        Excel::import(new UserImport, $file);

        return back()->with('success', 'Data user berhasil diimpor.');
    }

    /**
     * Preview / Dry-run import untuk file SISTER (Data_dosen.xlsx).
     * Tidak melakukan perubahan apapun ke database.
     * Selalu menjalankan dalam Mode Aman (role & gelar tidak disentuh).
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $filename = strtolower($file->getClientOriginalName());

        if (!str_contains($filename, 'dosen') && !str_contains($filename, 'sister')) {
            return response()->json([
                'success' => false,
                'message' => 'File ini tidak terdeteksi sebagai export SISTER. Gunakan importer biasa.',
            ], 422);
        }

        $importer = new SisterDosenUserImport(true); // dry-run mode
        Excel::import($importer, $file);

        $results = $importer->getDryRunResults();
        $createCount = collect($results)->where('action', 'CREATE')->count();
        $updateCount = collect($results)->where('action', 'UPDATE')->count();

        return response()->json([
            'success' => true,
            'message' => 'Simulasi import berhasil. Tidak ada data yang diubah.',
            'summary' => [
                'total' => count($results),
                'create' => $createCount,
                'update' => $updateCount,
                'skipped' => $importer->getErrorCount(), // reuse error count field for now
            ],
            'results' => $results,
            'mode_aman_note' => 'Mode Aman aktif: role tidak akan diubah, gelar_depan/gelar_belakang dibiarkan kosong untuk diisi manual.',
        ]);
    }
}
