<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private function defaultPassword(): string
    {
        return env('SEEDER_DEFAULT_PASSWORD', 'password');
    }

    private function createUser(array $data, string $role): User
    {
        $data['password'] = bcrypt($data['password'] ?? $this->defaultPassword());
        $data['is_active'] = true;

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            $data
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsByModule = [
            'master-data' => ['view', 'create', 'edit', 'delete'],
            'portofolio' => ['view', 'create', 'edit', 'delete', 'verify'],
            'bkd' => ['view', 'create', 'edit', 'delete', 'verify'],
            'dokumen' => ['view', 'upload', 'edit', 'delete', 'verify'],
            'akreditasi' => ['view', 'edit', 'generate'],
            'spmi' => ['view', 'create', 'edit', 'delete'],
            'kurikulum' => ['view', 'create', 'edit', 'delete'],
            'sarpras' => ['view', 'create', 'edit', 'delete'],
            'mahasiswa' => ['view', 'create', 'edit', 'delete'],
            'kerjasama' => ['view', 'create', 'edit', 'delete'],
            'keuangan' => ['view', 'create', 'edit'],
            'rkat' => ['view', 'create', 'approve', 'configure'],
            'iku' => ['view', 'create', 'edit', 'delete'],
            'cascading' => ['view', 'create', 'edit'],
            'data-import' => ['download-template', 'upload', 'view'],
            'reconciliation' => ['view', 'approve', 'reject'],
            'agent-ai' => ['view', 'trigger', 'configure'],
            'laporan' => ['view', 'export', 'generate'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'admin' => ['view', 'create', 'edit', 'delete', 'upload'],
        ];

        foreach ($permissionsByModule as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$module}.{$action}", 'web');
            }
        }

        $roles = [
            'Super Admin' => ['all' => true],
            'Rektor' => ['akreditasi' => ['view', 'generate'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view'], 'data-import' => ['download-template', 'view'], 'reconciliation' => ['view'], 'rkat' => ['view'], 'iku' => ['view'], 'cascading' => ['view']],
            'WR 1 Akademik' => ['master-data' => ['view'], 'portofolio' => ['view'], 'bkd' => ['view'], 'akreditasi' => ['view'], 'kurikulum' => ['view'], 'data-import' => ['download-template', 'view']],
            'WR 2 Keuangan & Sarpras' => ['sarpras' => ['view'], 'keuangan' => ['view'], 'laporan' => ['view'], 'data-import' => ['download-template', 'view'], 'rkat' => ['view', 'approve'], 'iku' => ['view'], 'cascading' => ['view']],
            'LPM' => ['master-data' => ['view'], 'akreditasi' => ['view', 'edit', 'generate'], 'spmi' => ['view', 'create', 'edit', 'delete'], 'laporan' => ['view', 'export', 'generate'], 'agent-ai' => ['view', 'trigger'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view', 'approve', 'reject'], 'rkat' => ['view', 'create', 'approve', 'configure'], 'iku' => ['view', 'create', 'edit', 'delete'], 'cascading' => ['view', 'create', 'edit']],
            'Kepala LPPM' => ['master-data' => ['view'], 'portofolio' => ['view', 'verify'], 'bkd' => ['view'], 'agent-ai' => ['view', 'trigger'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view', 'approve', 'reject']],
            'Staf LPPM' => ['portofolio' => ['view'], 'dokumen' => ['view', 'upload'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view']],
            'Kepala Kerjasama' => ['master-data' => ['view'], 'kerjasama' => ['view', 'create', 'edit', 'delete'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view', 'approve', 'reject']],
            'Staf Kerjasama' => ['kerjasama' => ['view', 'create', 'edit'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view']],
            'Dekan' => ['master-data' => ['view'], 'portofolio' => ['view'], 'bkd' => ['view'], 'akreditasi' => ['view'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view'], 'data-import' => ['download-template', 'view'], 'rkat' => ['view', 'approve'], 'iku' => ['view'], 'cascading' => ['view']],
            'Kaprodi' => ['master-data' => ['view', 'create', 'edit'], 'portofolio' => ['view', 'create'], 'bkd' => ['view', 'create'], 'dokumen' => ['view', 'upload'], 'akreditasi' => ['view', 'generate'], 'spmi' => ['view'], 'kurikulum' => ['view', 'edit'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view', 'trigger'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view', 'approve', 'reject'], 'rkat' => ['view', 'create', 'approve'], 'iku' => ['view'], 'cascading' => ['view']],
            'Staf Prodi' => ['master-data' => ['view'], 'portofolio' => ['view'], 'dokumen' => ['view', 'upload'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view'], 'rkat' => ['view'], 'iku' => ['view']],
            'Dosen' => ['portofolio' => ['view', 'create', 'edit'], 'bkd' => ['view', 'create'], 'dokumen' => ['view', 'upload'], 'data-import' => ['download-template', 'view'], 'rkat' => ['view'], 'iku' => ['view']],
            'Asesor Tamu' => ['akreditasi' => ['view'], 'laporan' => ['view']],
            'Bagian Akademik' => ['master-data' => ['view', 'create', 'edit'], 'kurikulum' => ['view', 'create', 'edit'], 'portofolio' => ['view'], 'data-import' => ['download-template', 'upload', 'view'], 'reconciliation' => ['view'], 'rkat' => ['view'], 'iku' => ['view']],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::findOrCreate($roleName, 'web');

            if (isset($rolePerms['all']) && $rolePerms['all']) {
                $role->givePermissionTo(Permission::all());
            } else {
                foreach ($rolePerms as $module => $actions) {
                    foreach ($actions as $action) {
                        $permName = "{$module}.{$action}";
                        $permission = Permission::where('name', $permName)->first();
                        if ($permission) {
                            $role->givePermissionTo($permission);
                        }
                    }
                }
            }
        }

        $this->createUser(
            ['name' => 'Super Admin', 'email' => 'admin@itsnu.ac.id'],
            'Super Admin'
        );

        $this->createUser(
            ['name' => 'Kaprodi S1 Informatika', 'email' => 'kaprodi@itsnu.ac.id'],
            'Kaprodi'
        );

        $this->createUser(
            ['name' => 'Dosen A', 'email' => 'dosen@itsnu.ac.id'],
            'Dosen'
        );

        $multi = $this->createUser(
            ['name' => 'Dr. Ahmad (Multi-Role)', 'email' => 'multi@itsnu.ac.id'],
            'Dosen'
        );
        $multi->assignRole('Kaprodi', 'Dekan');

        $this->createUser(
            ['name' => 'Dekan Fakultas', 'email' => 'fakultas@itsnu.ac.id'],
            'Dekan'
        );
    }
}
