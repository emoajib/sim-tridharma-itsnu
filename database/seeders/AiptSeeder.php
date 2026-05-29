<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use App\Models\AiptMetric;
use App\Models\PeriodeAkademik;
use App\Models\SpmiCycle;
use App\Models\Prodi;
use App\Models\InstrumenAkreditasi;
use Illuminate\Database\Seeder;

class AiptSeeder extends Seeder
{
    public function run(): void
    {
        $periode = PeriodeAkademik::first();
        $prodi = Prodi::first();
        $instrumen = InstrumenAkreditasi::first();

        // 4 Aspek BAN-PT 4.0
        $data = [
            ['aspek' => 'Budaya Mutu', 'indikator' => 'Implementasi PPEPP', 'skor' => 3.5, 'status' => 'hijau'],
            ['aspek' => 'Budaya Mutu', 'indikator' => 'Ketersediaan Dokumen SPMI', 'skor' => 4.0, 'status' => 'hijau'],
            ['aspek' => 'Relevansi', 'indikator' => 'Keterserapan Lulusan', 'skor' => 2.8, 'status' => 'kuning'],
            ['aspek' => 'Relevansi', 'indikator' => 'Kerjasama Industri', 'skor' => 3.2, 'status' => 'hijau'],
            ['aspek' => 'Akuntabilitas', 'indikator' => 'Opini Laporan Keuangan', 'skor' => 4.0, 'status' => 'hijau'],
            ['aspek' => 'Diferensiasi Misi', 'indikator' => 'Keunggulan Lokal (Batik)', 'skor' => 3.8, 'status' => 'hijau'],
        ];

        foreach ($data as $item) {
            AiptMetric::firstOrCreate(
                ['indikator' => $item['indikator'], 'periode_id' => $periode->id],
                [
                    'aspek' => $item['aspek'],
                    'skor_saat_ini' => $item['skor'],
                    'status' => $item['status'],
                    'periode_id' => $periode->id,
                ]
            );
        }

        // SPMI Cycles
        $cycles = [
            ['tahap' => 'Penetapan', 'nama' => 'Standar Mutu 2026', 'progress' => 100, 'status' => 'completed'],
            ['tahap' => 'Pelaksanaan', 'nama' => 'Perkuliahan Ganjil 2026', 'progress' => 80, 'status' => 'on_progress'],
            ['tahap' => 'Evaluasi', 'nama' => 'Audit Internal 2026', 'progress' => 45, 'status' => 'on_progress'],
        ];

        foreach ($cycles as $c) {
            SpmiCycle::firstOrCreate(
                [
                    'nama_siklus' => $c['nama'],
                    'prodi_id' => $prodi?->id,
                    'periode_id' => $periode?->id,
                ],
                [
                    'tahap' => $c['tahap'],
                    'persentase_selesai' => $c['progress'],
                    'status' => $c['status'],
                    'tanggal_mulai' => now()->subMonths(3),
                    'kategori' => 'Akademik',
                    'instrumen_id' => $instrumen?->id,
                ]
            );
        }
    }
}
