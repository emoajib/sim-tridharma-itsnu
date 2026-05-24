<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class DataAkademikSeeder extends Seeder
{
    public function run(): void
    {
        // Create Faculties (updateOrCreate to handle existing records)
        $saintek = Fakultas::updateOrCreate(
            ['kode_fakultas' => 'SAINTEK'],
            ['nama_fakultas' => 'Fakultas SAINTEK', 'is_active' => true]
        );

        $dekabita = Fakultas::updateOrCreate(
            ['kode_fakultas' => 'DEKABITA'],
            ['nama_fakultas' => 'Fakultas DEKABITA', 'is_active' => true]
        );

        // Create Prodi for SAINTEK (updateOrCreate to rename existing)
        Prodi::updateOrCreate(
            ['kode_prodi' => 'FIS'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Fisika', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'IF'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Informatika', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'TI'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Teknik Industri', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'ARS'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Arsitektur', 'jenjang' => 'S1', 'is_active' => true]
        );

        // Create Prodi for DEKABITA (updateOrCreate to rename existing)
        Prodi::updateOrCreate(
            ['kode_prodi' => 'AK'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Akuntansi', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'MJ'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Manajemen', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'EP'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Ekonomi Pembangunan', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'EB'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Ekonomi Bisnis', 'jenjang' => 'S1', 'is_active' => true]
        );

        // Additional Prodi for SAINTEK with akreditasi
        Prodi::updateOrCreate(
            ['kode_prodi' => 'TTI'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Teknologi Informasi', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'tanggal_kadaluarsa' => '2028-06-30', 'is_active' => true]
        );

        // Additional Prodi for DEKABITA with akreditasi
        Prodi::updateOrCreate(
            ['kode_prodi' => 'BD'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Bisnis Digital', 'jenjang' => 'S1', 'akreditasi' => 'Baik', 'tanggal_kadaluarsa' => '2027-06-30', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'AKT'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'D3 Akuntansi', 'jenjang' => 'D3', 'akreditasi' => 'Baik', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'AP'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'D3 Administrasi Perkantoran', 'jenjang' => 'D3', 'akreditasi' => 'Baik', 'is_active' => true]
        );

        Prodi::updateOrCreate(
            ['kode_prodi' => 'KB'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'D3 Kriya Batik', 'jenjang' => 'D3', 'akreditasi' => 'Baik', 'is_active' => true]
        );

        // Create Periode Akademik
        $tahunSekarang = date('Y');

        PeriodeAkademik::firstOrCreate(
            ['kode_periode' => $tahunSekarang.'-Ganjil'],
            ['nama_periode' => 'Semester Ganjil '.$tahunSekarang, 'tanggal_mulai' => $tahunSekarang.'-01-01', 'tanggal_selesai' => $tahunSekarang.'-06-30', 'is_active' => true]
        );

        PeriodeAkademik::firstOrCreate(
            ['kode_periode' => $tahunSekarang.'-Genap'],
            ['nama_periode' => 'Semester Genap '.$tahunSekarang, 'tanggal_mulai' => $tahunSekarang.'-07-01', 'tanggal_selesai' => $tahunSekarang.'-12-31', 'is_active' => true]
        );

        echo "Data dummy berhasil dibuat:\n";
        echo "- 2 Fakultas (SAINTEK, DEKABITA)\n";
        echo "- 15 Prodi\n";
        echo "- 2 Periode Akademik\n";
    }
}
