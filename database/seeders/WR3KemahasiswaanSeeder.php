<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WR3KemahasiswaanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('m_kategori_prestasi')->insertOrIgnore([
            ['nama_kategori' => 'Akademik', 'jenis' => 'Akademik', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Olahraga', 'jenis' => 'Non-Akademik', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Seni', 'jenis' => 'Non-Akademik', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Keagamaan', 'jenis' => 'Non-Akademik', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('m_ormawa')->insertOrIgnore([
            ['nama' => 'BEM Universitas', 'kategori' => 'BEM', 'prodi_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'DPM', 'kategori' => 'DPM', 'prodi_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'UKM Olahraga', 'kategori' => 'UKM', 'prodi_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
