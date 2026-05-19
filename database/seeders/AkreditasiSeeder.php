<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = DB::table('m_prodi')->get();
        $periodes = DB::table('m_periode_akademik')->get();
        $indikators = DB::table('m_indikator_akreditasi')->get();

        if ($prodis->isEmpty()) {
            $this->command->warn('Prodi kosong, lewati seeding akreditasi.');
            return;
        }

        foreach ($prodis as $prodi) {
            foreach ($periodes as $periode) {
                DB::table('m_kuisioner_tracer')->insert([
                    'prodi_id' => $prodi->id,
                    'judul_kuisioner' => 'Tracer Study ' . $prodi->nama_prodi . ' ' . $periode->nama_periode,
                    'tahun' => (string)now()->year,
                    'pertanyaan' => json_encode([
                        ['id' => 'q1', 'text' => 'Seberapa seringkah Anda mendapat pekerjaan?'],
                        ['id' => 'q2', 'text' => 'Berapa gaji pertama Anda?'],
                        ['id' => 'q3', 'text' => 'Apakah pekerjaan Anda sesuai dengan bidang studi?'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($prodis as $prodi) {
            foreach ($periodes->take(2) as $periode) {
                $totalSkor = 0;
                $count = 0;

                foreach ($indikators as $indikator) {
                    $nilai = rand(10, 100);
                    $status = $nilai >= 75 ? 'hijau' : ($nilai >= 50 ? 'kuning' : 'merah');

                    DB::table('trx_pemenuhan_indikator')->insert([
                        'prodi_id' => $prodi->id,
                        'periode_id' => $periode->id,
                        'indikator_id' => $indikator->id,
                        'nilai' => $nilai,
                        'status' => $status,
                        'capaian' => 'Capaian indikator: ' . $nilai . '%',
                        'bukti' => 'Dokumen bukti #' . rand(100, 999),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalSkor += $nilai * ($indikator->bobot / 100);
                    $count++;
                }

                $skorAkhir = $count > 0 ? round($totalSkor / ($count / 100), 2) : 0;
                $predikat = $skorAkhir >= 85 ? 'Unggul' : ($skorAkhir >= 70 ? 'Baik Sekali' : ($skorAkhir >= 50 ? 'Baik' : 'Tidak Terakreditasi'));

                DB::table('trx_skor_akreditasi')->insert([
                    'prodi_id' => $prodi->id,
                    'periode_id' => $periode->id,
                    'skor_total' => $skorAkhir,
                    'skor_simulasi' => $skorAkhir + rand(-5, 5),
                    'predikat' => $predikat,
                    'lembaga_akreditasi' => 'BAN-PT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                for ($i = 0; $i < rand(2, 4); $i++) {
                    DB::table('trx_audit_mutu')->insert([
                        'prodi_id' => $prodi->id,
                        'periode_id' => $periode->id,
                        'temuan' => 'Temuan Audit #' . ($i + 1) . ': ' . Str::random(50),
                        'kategori_temuan' => ['Ketidaksesuaian Minor', 'Ketidaksesuaian Major', 'Observasi'][rand(0, 2)],
                        'rekomendasi' => 'Rekomendasi perbaikan untuk temuan #' . ($i + 1),
                        'status' => ['Open', 'In Progress', 'Closed'][rand(0, 2)],
                        'tanggal_audit' => now()->subDays(rand(1, 60)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                for ($i = 0; $i < rand(3, 6); $i++) {
                    $dampak = rand(1, 5);
                    $probabilitas = rand(1, 5);
                    $tingkatRisiko = $dampak * $probabilitas;

                    DB::table('trx_risk_register')->insert([
                        'prodi_id' => $prodi->id,
                        'periode_id' => $periode->id,
                        'nama_risiko' => 'Risiko #' . ($i + 1) . ': ' . Str::random(30),
                        'kategori' => ['Akademik', 'Keuangan', 'SDM', 'Sarana', 'Mutu'][rand(0, 4)],
                        'dampak' => $dampak,
                        'probabilitas' => $probabilitas,
                        'tingkat_risiko' => $tingkatRisiko,
                        'mitigasi' => 'Rencana mitigasi untuk risiko #' . ($i + 1),
                        'status_risiko' => ['Teridentifikasi', 'Dalam Mitigasi', 'Terealisasi'][rand(0, 2)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Akreditasi data seeded successfully.');
    }
}