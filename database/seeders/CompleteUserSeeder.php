<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        
        $roles = [
            1 => ['name' => 'Super Admin', 'email' => 'superadmin@itsnu.ac.id'],
            2 => ['name' => 'Rektor', 'email' => 'rektor@itsnu.ac.id'],
            3 => ['name' => 'Wakil Rektor 1', 'email' => 'wr1@itsnu.ac.id'],
            4 => ['name' => 'Wakil Rektor 2', 'email' => 'wr2@itsnu.ac.id'],
            5 => ['name' => 'LPM', 'email' => 'lpm@itsnu.ac.id'],
            6 => ['name' => 'Kepala LPPM', 'email' => 'lppm.kepala@itsnu.ac.id'],
            7 => ['name' => 'Staf LPPM', 'email' => 'lppm.staf@itsnu.ac.id'],
            8 => ['name' => 'Kepala Lembaga Kerjasama', 'email' => 'kerjasama.kepala@itsnu.ac.id'],
            9 => ['name' => 'Staf Kerjasama', 'email' => 'kerjasama.staf@itsnu.ac.id'],
            10 => ['name' => 'Dekan', 'email' => 'dekan@itsnu.ac.id'],
            11 => ['name' => 'Kaprodi', 'email' => 'kaprodi@itsnu.ac.id'],
            12 => ['name' => 'Staf Prodi', 'email' => 'prodi.staf@itsnu.ac.id'],
            13 => ['name' => 'Dosen', 'email' => 'dosen@itsnu.ac.id'],
            14 => ['name' => 'Asesor Tamu', 'email' => 'asesor@itsnu.ac.id'],
            15 => ['name' => 'Bagian Akademik', 'email' => 'akademik@itsnu.ac.id'],
        ];

        foreach ($roles as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'email_verified_at' => now(),
                    'is_active' => true
                ]
            );

            // Mapping blueprint name to internal role name if different
            $roleName = $data['name'];
            if ($roleName === 'Wakil Rektor 1') $roleName = 'WR 1 Akademik';
            if ($roleName === 'Wakil Rektor 2') $roleName = 'WR 2 Keuangan & Sarpras';
            if ($roleName === 'Kepala Lembaga Kerjasama') $roleName = 'Kepala Kerjasama';
            if ($roleName === 'Staf Kerjasama') $roleName = 'Staf Kerjasama';

            $user->syncRoles([$roleName]);
        }
    }
}
