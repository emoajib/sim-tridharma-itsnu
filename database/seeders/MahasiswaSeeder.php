<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Guard: skip if mahasiswa already seeded
        if (DB::table('m_mahasiswa')->count() > 0) {
            return;
        }

        $prodis = DB::table('m_prodi')->get();

        if ($prodis->isEmpty()) {
            return;
        }

        foreach ($prodis as $prodi) {
            for ($i = 0; $i < rand(20, 50); $i++) {
                $angkatan = rand(2020, 2025);
                $nim = $angkatan.str_pad($prodi->id, 2, '0', STR_PAD_LEFT).str_pad($i + 1, 3, '0', STR_PAD_LEFT);

                DB::table('m_mahasiswa')->insertOrIgnore([
                    'prodi_id' => $prodi->id,
                    'nim' => $nim,
                    'nama' => 'Mahasiswa '.Str::random(10),
                    'angkatan' => (string) $angkatan,
                    'status' => 'aktif',
                    'email' => Str::random(5).'@student.itsnu.ac.id',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
