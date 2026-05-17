<?php

namespace Database\Seeders;

use App\Models\LembagaAkreditasi;
use App\Models\InstrumenAkreditasi;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class AccreditationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Lembaga
        $banpt = LembagaAkreditasi::updateOrCreate(['singkatan' => 'BAN-PT'], ['nama_lembaga' => 'Badan Akreditasi Nasional Perguruan Tinggi']);
        $infokom = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAM INFOKOM'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Informatika dan Komputer']);
        $lamemba = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAMEMBA'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Ekonomi Manajemen Bisnis dan Akuntansi']);

        // 2. Buat Instrumen
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $banpt->id, 'nama_instrumen' => 'IAPS 4.0 / IAPT 3.0']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $infokom->id, 'nama_instrumen' => 'Instrumen Lamsama/Infokom v2.1']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $lamemba->id, 'nama_instrumen' => 'Instrumen Lamemba v2.0']);

        // 3. Ploting Prodi ke Lembaga (Fixing all codes for ITSNU)
        Prodi::whereIn('kode_prodi', ['IF', 'TTI', 'TI'])->update(['lembaga_akreditasi_id' => $infokom->id]);
        Prodi::whereIn('kode_prodi', ['BD', 'AKT', 'AP'])->update(['lembaga_akreditasi_id' => $lamemba->id]);
        Prodi::whereIn('kode_prodi', ['FIS', 'TIND', 'KB'])->update(['lembaga_akreditasi_id' => $banpt->id]);
    }
}
