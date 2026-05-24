<?php

namespace Database\Seeders;

use App\Models\InstrumenAkreditasi;
use App\Models\LembagaAkreditasi;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class AccreditationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat / Update Lembaga
        $banpt = LembagaAkreditasi::updateOrCreate(['singkatan' => 'BAN-PT'], ['nama_lembaga' => 'Badan Akreditasi Nasional Perguruan Tinggi', 'is_active' => true]);
        $infokom = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAM INFOKOM'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Informatika dan Komputer', 'is_active' => true]);
        $lamemba = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAMEMBA'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Ekonomi Manajemen Bisnis dan Akuntansi', 'is_active' => true]);
        $lamTeknik = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAM Teknik'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Program Studi Teknik', 'is_active' => true]);
        $lamsama = LembagaAkreditasi::updateOrCreate(['singkatan' => 'LAMSAMA'], ['nama_lembaga' => 'Lembaga Akreditasi Mandiri Sains Alam dan Ilmu Formal', 'is_active' => true]);

        // 2. Buat Instrumen Default
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $banpt->id, 'nama_instrumen' => 'IAPS 4.0 / IAPT 3.0']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $infokom->id, 'nama_instrumen' => 'Instrumen LAM INFOKOM v1.0']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $lamemba->id, 'nama_instrumen' => 'Instrumen LAMEMBA v2.0']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $lamTeknik->id, 'nama_instrumen' => 'Instrumen LAM Teknik v1.0']);
        InstrumenAkreditasi::updateOrCreate(['lembaga_id' => $lamsama->id, 'nama_instrumen' => 'Instrumen LAMSAMA v1.0']);

        // 3. Ploting Prodi ke Lembaga (Sesuai Struktur ITSNU Pekalongan)

        // LAM INFOKOM: S1 Teknologi Informasi (TTI), S1 Informatika (IF)
        Prodi::whereIn('kode_prodi', ['IF', 'TTI'])->update(['lembaga_akreditasi_id' => $infokom->id]);

        // LAM Teknik: S1 Teknik Industri (TI)
        Prodi::whereIn('kode_prodi', ['TI'])->update(['lembaga_akreditasi_id' => $lamTeknik->id]);

        // LAMEMBA: S1 Bisnis Digital (BD), D3 Akuntansi (AKT), D3 Administrasi Perkantoran (AP)
        Prodi::whereIn('kode_prodi', ['BD', 'AKT', 'AP'])->update(['lembaga_akreditasi_id' => $lamemba->id]);

        // LAMSAMA: S1 Fisika (FIS)
        Prodi::whereIn('kode_prodi', ['FIS'])->update(['lembaga_akreditasi_id' => $lamsama->id]);

        // BAN-PT: D3 Kriya Batik (KB) + default untuk prodi lain
        Prodi::whereIn('kode_prodi', ['KB'])->update(['lembaga_akreditasi_id' => $banpt->id]);
        Prodi::whereNull('lembaga_akreditasi_id')->update(['lembaga_akreditasi_id' => $banpt->id]);

        echo "✅ Ploting Lembaga Akreditasi berhasil diperbarui.\n";
    }
}
