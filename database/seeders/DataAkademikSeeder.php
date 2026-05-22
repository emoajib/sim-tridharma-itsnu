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
        // Create Faculties
        $saintek = Fakultas::firstOrCreate(
            ['kode_fakultas' => 'SAINTEK'],
            ['nama_fakultas' => 'Fakultas Sains dan Teknologi', 'is_active' => true]
        );

        $dekabita = Fakultas::firstOrCreate(
            ['kode_fakultas' => 'DEKABITA'],
            ['nama_fakultas' => 'Fakultas Ekonomi dan Bisnis', 'is_active' => true]
        );

        // Create Prodi for SAINTEK
        Prodi::firstOrCreate(
            ['kode_prodi' => 'FIS'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'Fisika', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'IF'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'Informatika', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'TI'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'Teknik Industri', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'ARS'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'Arsitektur', 'jenjang' => 'S1', 'is_active' => true]
        );

        // Create Prodi for DEKABITA
        Prodi::firstOrCreate(
            ['kode_prodi' => 'AK'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'Akuntansi', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'MJ'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'Manajemen', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'EP'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'Ekonomi Pembangunan', 'jenjang' => 'S1', 'is_active' => true]
        );

        Prodi::firstOrCreate(
            ['kode_prodi' => 'EB'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'Ekonomi Bisnis', 'jenjang' => 'S1', 'is_active' => true]
        );

        $dekabita = Fakultas::firstOrCreate(
            ['kode_fakultas' => 'DEKABITA'],
            ['nama_fakultas' => 'Fakultas Ekonomi dan Bisnis', 'is_active' => true]
        );

        // Additional Prodi for SAINTEK with akreditasi
        Prodi::firstOrCreate(
            ['kode_prodi' => 'TTI'],
            ['fakultas_id' => $saintek->id, 'nama_prodi' => 'S1 Teknologi Informasi', 'jenjang' => 'S1', 'akreditasi' => 'Baik Sekali', 'tanggal_kadaluarsa' => '2028-06-30', 'is_active' => true]
        );

        // Additional Prodi for DEKABITA with akreditasi
        Prodi::firstOrCreate(
            ['kode_prodi' => 'BD'],
            ['fakultas_id' => $dekabita->id, 'nama_prodi' => 'S1 Bisnis Digital', 'jenjang' => 'S1', 'akreditasi' => 'Baik', 'tanggal_kadaluarsa' => '2027-06-30', 'is_active' => true]
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
        echo "- 12 Prodi\n";
        echo "- 2 Periode Akademik\n";
    }
}
