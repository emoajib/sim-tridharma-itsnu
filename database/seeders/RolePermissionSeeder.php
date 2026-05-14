<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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
            'agent-ai' => ['view', 'trigger', 'configure'],
            'laporan' => ['view', 'export', 'generate'],
            'users' => ['view', 'create', 'edit', 'delete'],
        ];

        foreach ($permissionsByModule as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }

        $roles = [
            'Super Admin' => ['all' => true],
            'Rektor' => ['akreditasi' => ['view', 'generate'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view']],
            'WR 1 Akademik' => ['master-data' => ['view'], 'portofolio' => ['view'], 'bkd' => ['view'], 'akreditasi' => ['view'], 'kurikulum' => ['view']],
            'WR 2 Keuangan & Sarpras' => ['sarpras' => ['view'], 'keuangan' => ['view'], 'laporan' => ['view']],
            'LPM' => ['master-data' => ['view'], 'akreditasi' => ['view', 'edit', 'generate'], 'spmi' => ['view', 'create', 'edit', 'delete'], 'laporan' => ['view', 'export', 'generate'], 'agent-ai' => ['view', 'trigger']],
            'Kepala LPPM' => ['master-data' => ['view'], 'portofolio' => ['view', 'verify'], 'bkd' => ['view'], 'agent-ai' => ['view', 'trigger']],
            'Staf LPPM' => ['portofolio' => ['view'], 'dokumen' => ['view', 'upload']],
            'Kepala Kerjasama' => ['master-data' => ['view'], 'kerjasama' => ['view', 'create', 'edit', 'delete'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view']],
            'Staf Kerjasama' => ['kerjasama' => ['view', 'create', 'edit']],
            'Dekan' => ['master-data' => ['view'], 'portofolio' => ['view'], 'bkd' => ['view'], 'akreditasi' => ['view'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view']],
            'Kaprodi' => ['master-data' => ['view', 'create', 'edit'], 'portofolio' => ['view', 'create'], 'bkd' => ['view', 'create'], 'dokumen' => ['view', 'upload'], 'akreditasi' => ['view', 'generate'], 'spmi' => ['view'], 'kurikulum' => ['view', 'edit'], 'laporan' => ['view', 'export'], 'agent-ai' => ['view', 'trigger']],
            'Staf Prodi' => ['master-data' => ['view'], 'portofolio' => ['view'], 'dokumen' => ['view', 'upload']],
            'Dosen' => ['portofolio' => ['view', 'create', 'edit'], 'bkd' => ['view', 'create'], 'dokumen' => ['view', 'upload']],
            'Asesor Tamu' => ['akreditasi' => ['view'], 'laporan' => ['view']],
            'Bagian Akademik' => ['master-data' => ['view', 'create', 'edit'], 'kurikulum' => ['view', 'create', 'edit'], 'portofolio' => ['view']],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);

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

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@itsnu.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('Super Admin');

        $kaprodi = User::create([
            'name' => 'Kaprodi S1 Informatika',
            'email' => 'kaprodi@itsnu.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $kaprodi->assignRole('Kaprodi');

        $dosen = User::create([
            'name' => 'Dosen A',
            'email' => 'dosen@itsnu.ac.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $dosen->assignRole('Dosen');
    }
}
