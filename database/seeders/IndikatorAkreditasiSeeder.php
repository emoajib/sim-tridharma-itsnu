<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use App\Models\IndikatorAkreditasi;
use App\Models\InstrumenAkreditasi;
use Illuminate\Database\Seeder;

class IndikatorAkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        $instrumens = InstrumenAkreditasi::all();

        foreach ($instrumens as $instrumen) {
            $lembaga = $instrumen->lembaga;
            $data = $this->getIndikatorForLembaga($lembaga->singkatan, $instrumen->id);
            foreach ($data as $item) {
                IndikatorAkreditasi::firstOrCreate(
                    ['kode_indikator' => $item['kode_indikator']],
                    $item
                );
            }
        }

        $count = IndikatorAkreditasi::count();
        echo "✅ $count indikator akreditasi berhasil dibuat.\n";
    }

    private function getIndikatorForLembaga(string $lembaga, int $instrumenId): array
    {
        // 9 Kriteria standar akreditasi
        $kriteria = [
            ['kode' => 'VISI', 'nama' => 'Visi, Misi, Tujuan dan Strategi', 'bobot' => 5],
            ['kode' => 'TATA', 'nama' => 'Tata Pamong, Tata Kelola dan Kerjasama', 'bobot' => 10],
            ['kode' => 'MHS', 'nama' => 'Mahasiswa', 'bobot' => 8],
            ['kode' => 'SDM', 'nama' => 'Sumber Daya Manusia', 'bobot' => 15],
            ['kode' => 'KUR', 'nama' => 'Kurikulum, Pembelajaran dan Suasana Akademik', 'bobot' => 15],
            ['kode' => 'PEM', 'nama' => 'Pembiayaan, Sarana dan Prasarana', 'bobot' => 12],
            ['kode' => 'PEN', 'nama' => 'Penelitian dan Pengabdian Masyarakat', 'bobot' => 12],
            ['kode' => 'LUL', 'nama' => 'Lulusan', 'bobot' => 13],
            ['kode' => 'MUTU', 'nama' => 'Sistem Penjaminan Mutu Internal', 'bobot' => 10],
        ];

        $prefix = str_replace(' ', '_', $lembaga);

        return array_map(fn($k) => [
            'instrumen_id' => $instrumenId,
            'kode_indikator' => $prefix . '_' . $k['kode'],
            'nama_indikator' => $k['nama'],
            'kriteria' => $k['kode'],
            'bobot' => $k['bobot'],
            'jenis_akreditasi' => $lembaga,
            'is_active' => true,
        ], $kriteria);
    }
}
