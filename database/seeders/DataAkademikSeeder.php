<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Database\Seeder;

class DataAkademikSeeder extends Seeder
{
    public function run(): void
    {
        // Create Faculties
        $saintek = Fakultas::create([
            'kode_fakultas' => 'SAINTEK',
            'nama_fakultas' => 'Fakultas Sains dan Teknologi',
            'is_active' => true,
        ]);

        $dekabita = Fakultas::create([
            'kode_fakultas' => 'DEKABITA',
            'nama_fakultas' => 'Fakultas Ekonomi dan Bisnis',
            'is_active' => true,
        ]);

        // Create Prodi for SAINTEK
        Prodi::create([
            'fakultas_id' => $saintek->id,
            'kode_prodi' => 'FIS',
            'nama_prodi' => 'S1 Fisika',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik Sekali',
            'tanggal_kadaluarsa' => '2028-12-31',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $saintek->id,
            'kode_prodi' => 'IF',
            'nama_prodi' => 'S1 Informatika',
            'jenjang' => 'S1',
            'akreditasi' => 'Unggul',
            'tanggal_kadaluarsa' => '2029-06-30',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $saintek->id,
            'kode_prodi' => 'TI',
            'nama_prodi' => 'S1 Teknik Industri',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik',
            'tanggal_kadaluarsa' => '2027-12-31',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $saintek->id,
            'kode_prodi' => 'TTI',
            'nama_prodi' => 'S1 Teknologi Informasi',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik Sekali',
            'tanggal_kadaluarsa' => '2028-06-30',
            'is_active' => true,
        ]);

        // Create Prodi for DEKABITA
        Prodi::create([
            'fakultas_id' => $dekabita->id,
            'kode_prodi' => 'BD',
            'nama_prodi' => 'S1 Bisnis Digital',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik',
            'tanggal_kadaluarsa' => '2027-06-30',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $dekabita->id,
            'kode_prodi' => 'AKT',
            'nama_prodi' => 'D3 Akuntansi',
            'jenjang' => 'D3',
            'akreditasi' => 'Baik Sekali',
            'tanggal_kadaluarsa' => '2029-12-31',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $dekabita->id,
            'kode_prodi' => 'AP',
            'nama_prodi' => 'D3 Administrasi Perkantoran',
            'jenjang' => 'D3',
            'akreditasi' => 'Baik',
            'tanggal_kadaluarsa' => '2028-12-31',
            'is_active' => true,
        ]);

        Prodi::create([
            'fakultas_id' => $dekabita->id,
            'kode_prodi' => 'KB',
            'nama_prodi' => 'D3 Kriya Batik',
            'jenjang' => 'D3',
            'akreditasi' => 'Baik Sekali',
            'tanggal_kadaluarsa' => '2029-06-30',
            'is_active' => true,
        ]);

        // Create Periode Akademik
        $tahunSekarang = date('Y');
        
        PeriodeAkademik::create([
            'kode_periode' => $tahunSekarang . '-Ganjil',
            'nama_periode' => 'Semester Ganjil ' . $tahunSekarang,
            'tanggal_mulai' => $tahunSekarang . '-01-01',
            'tanggal_selesai' => $tahunSekarang . '-06-30',
            'is_active' => true,
        ]);

        PeriodeAkademik::create([
            'kode_periode' => $tahunSekarang . '-Genap',
            'nama_periode' => 'Semester Genap ' . $tahunSekarang,
            'tanggal_mulai' => $tahunSekarang . '-07-01',
            'tanggal_selesai' => $tahunSekarang . '-12-31',
            'is_active' => true,
        ]);

        echo "Data dummy berhasil dibuat:\n";
        echo "- 2 Fakultas (SAINTEK, DEKABITA)\n";
        echo "- 8 Prodi\n";
        echo "- 2 Periode Akademik\n";
    }
}