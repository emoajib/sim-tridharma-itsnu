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

        // Guard yang sama seperti update()/syncRoles(): tanpa ini, admin biasa
        // bisa POST /admin/users dengan role_ids Super Admin dan membuat
        // akun Super Admin baru.
        if (! empty($validated['role_ids'])) {
            $this->guardPrivilegedRoleMutation(
                $request,
                Role::whereIn('id', $validated['role_ids'])->pluck('name')->toArray()
            );
        }

        // Non-Super-Admin tidak boleh membuat user di luar prodi-nya
        if (! $request->user()->hasRole('Super Admin')
            && ! empty($validated['prodi_id'])
            && (int) $validated['prodi_id'] !== (int) ($request->user()->prodi_id ?? 0)) {
            abort(403, 'Hanya Super Admin yang dapat membuat user di program studi lain.');
        }

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
        $this->guardUserScope($request, $user);

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

        // Guard escalation:-this request boleh menaikkan role target ke role
        // lintas-fakultas? Kalau tidak, tolak SEBELUM touch data.
        if (! empty($validated['role_ids'])) {
            $this->guardPrivilegedRoleMutation(
                $request,
                Role::whereIn('id', $validated['role_ids'])->pluck('name')->toArray()
            );
        }

        // Non-Super-Admin tidak boleh memindahkan user ke prodi lain
        if (! $request->user()->hasRole('Super Admin')
            && array_key_exists('prodi_id', $validated)
            && ! empty($validated['prodi_id'])
            && (int) $validated['prodi_id'] !== (int) $user->prodi_id) {
            abort(403, 'Hanya Super Admin yang dapat memindahkan user antar program studi.');
        }

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

    /**
     * Role yang memberiKuasa lintas-fakultas / lintas-prodi.
     * Hanya Super Admin boleh memberikan atau mencabut role ini.
     */
    private const PRIVILEGED_ROLES = [
        'Super Admin',
        'Rektor',
        'WR 1 Akademik',
        'WR 2 Keuangan & Sarpras',
        'WR 3 Kemahasiswaan',
        'LPM',
        'Kepala LPPM',
        'Staf LPPM',
        'Kepala Lembaga Kerjasama',
        'Staf Kerjasama',
        'Bagian Akademik',
    ];

    /**
     * Cegah privilege escalation: role hierarkis hanya boleh diubah oleh Super Admin.
     * Tanpa guard ini, admin biasa bisa memanggil sync-roles dengan role_ids
     * berisi Super Admin dan membuat user mana pun jadi Super Admin.
     */
    private function guardPrivilegedRoleMutation(Request $request, array $roleNames): void
    {
        $touchesPrivileged = array_intersect($roleNames, self::PRIVILEGED_ROLES);

        if ($touchesPrivileged === []) {
            return;
        }

        if (! $request->user()?->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat memberikan roleAcross-fakultas.');
        }
    }

    /**
     * Cegah cross-tenant: selain Super Admin, hanya boleh mengelola user
     * di prodi sendiri. Tanpa ini, Kaprodi prodi A bisa edit/hapus user prodi B
     * hanya dengan Systemicroute binding {user}.
     */
    private function guardUserScope(Request $request, User $target): void
    {
        $actor = $request->user();

        if (! $actor) {
            abort(403);
        }

        if ($actor->hasRole('Super Admin')) {
            return;
        }

        // Cegah modifikasi akun yang saat ini punya role privileged
        if (array_intersect($target->getRoleNames()->toArray(), self::PRIVILEGED_ROLES) !== []) {
            abort(403, 'Tidak dapat mengelola akun dengan role lintas-fakultas.');
        }

        $actorProdi = $actor->prodi_id ?? null;
        $targetProdi = $target->prodi_id ?? null;

        // Admin yang punya prodi hanya boleh mengelola user prodi-nya sendiri
        if ($actorProdi !== null && (int) $actorProdi !== (int) $targetProdi) {
            abort(403, 'Akun ini berada di luar lingkup program studi Anda.');
        }
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

    public function destroy(Request $request, User $user)
    {
        $this->guardUserScope($request, $user);

        // Jangan sampai admin menghapus akunnya sendiri
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi berbasis ROLE, bukan email. Sebelumnya hanya
        // $user->email === 'admin@itsnu.ac.id' yang dilindungi, jadi akun
        // Super Admin kedua (dengan email berbeda) tetap bisa dihapus.
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Tidak dapat menghapus akun Super Admin.');
        }

        $email = $user->email;

        $user->delete();

        Log::warning('User deleted', [
            'actor' => $request->user()?->email,
            'target' => $email,
        ]);

        return back()->with('success', "User '{$email}' berhasil dihapus.");
    }

    public function syncRoles(Request $request, User $user)
    {
        $this->guardUserScope($request, $user);

        $validated = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $roleNames = Role::whereIn('id', $validated['role_ids'])->pluck('name')->toArray();

        // Inti guard privilege escalation: role lintas-fakultas hanya boleh
        // diberikan oleh Super Admin.
        $this->guardPrivilegedRoleMutation($request, $roleNames);

        // Mencabut role privileged dari akun yang memilikinya juga perlu otorisasi
        $this->guardPrivilegedRoleMutation($request, $user->getRoleNames()->toArray());

        $from = $user->getRoleNames()->toArray();
        $user->syncRoles($roleNames);

        Log::warning('Role assignment changed', [
            'actor' => $request->user()?->email,
            'target' => $user->email,
            'from' => $from,
            'to' => $roleNames,
        ]);

        return response()->json([
            'success' => true,
            'roles' => $roleNames,
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
