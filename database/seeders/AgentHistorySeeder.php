<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgentHistorySeeder extends Seeder
{
    public function run(): void
    {
        $prodis = DB::table('m_prodi')->get();
        $dosens = DB::table('m_dosen')->get();
        $periodes = DB::table('m_periode_akademik')->get();

        if ($prodis->isEmpty()) {
            $this->command->warn('Prodi kosong, lewati seeding agent history.');
            return;
        }

        $agents = ['prediksi', 'rekomendasi', 'peringatan', 'verifikasi', 'generator', 'integrasi'];
        $agentStatuses = ['success', 'success', 'success', 'failed', 'running'];
        $rekomendasiStatuses = ['pending', 'in_progress', 'completed', 'rejected'];
        $peringatanLevels = ['critical', 'warning', 'info'];
        $verifikasiStatuses = ['verified', 'pending', 'rejected'];
        $docTypes = ['LED', 'LKPT', 'SK', 'Matriks'];

        foreach ($agents as $agent) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                $startTime = now()->subMinutes(rand(1, 1000));
                $duration = rand(500, 15000);

                DB::table('agent_execution_log')->insert([
                    'agent_name' => $agent,
                    'status' => $agentStatuses[array_rand($agentStatuses)],
                    'input_data' => json_encode(['prodi_id' => $prodis->random()->id, 'periode_id' => $periodes->random()?->id]),
                    'output_data' => json_encode(['result' => 'execution output']),
                    'triggered_by' => 'system',
                    'triggered_at' => $startTime,
                    'started_at' => $startTime,
                    'finished_at' => $startTime->addMilliseconds($duration),
                    'duration_ms' => $duration,
                    'error_message' => rand(0, 1) ? null : 'Error: ' . Str::random(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($prodis as $prodi) {
            foreach ($periodes->take(2) as $periode) {
                $skorPrediksi = rand(60, 100);
                $probUnggul = rand(20, 80) / 100;
                $probBaikSekali = rand(10, 50) / 100;
                $probBaik = 1 - $probUnggul - $probBaikSekali;

                DB::table('agent_prediction_history')->insert([
                    'prodi_id' => $prodi->id,
                    'periode_id' => $periode->id,
                    'skor_prediksi' => $skorPrediksi,
                    'prediksi_akreditasi' => $skorPrediksi >= 85 ? 'Unggul' : ($skorAkhir >= 70 ? 'Baik Sekali' : 'Baik'),
                    'probabilitas_unggul' => round($probUnggul, 2),
                    'probabilitas_baik_sekali' => round($probBaikSekali, 2),
                    'probabilitas_baik' => round($probBaik, 2),
                    'confidence_interval' => rand(80, 99) . '%',
                    'model_version' => 'v1.0',
                    'detail_data' => json_encode([
                        'historical_scores' => [rand(60, 90), rand(65, 85), rand(70, 90)],
                        'trend' => 'increasing',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $indikators = DB::table('m_indikator_akreditasi')->take(10)->get();
                foreach ($indikators as $indikator) {
                    DB::table('agent_rekomendasi_log')->insert([
                        'prodi_id' => $prodi->id,
                        'indikator_id' => $indikator->id,
                        'judul_rekomendasi' => 'Rekomendasi untuk ' . $indikator->kode_indikator,
                        'deskripsi' => 'Rekomendasi perbaikan untuk mencapai target akreditasi.',
                        'prioritas' => rand(1, 3),
                        'status' => $rekomendasiStatuses[array_rand($rekomendasiStatuses)],
                        'target_capai' => rand(70, 100) . '%',
                        'deadline' => now()->addDays(rand(30, 180))->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        foreach ($dosens as $dosen) {
            for ($i = 0; $i < rand(3, 8); $i++) {
                $level = $peringatanLevels[array_rand($peringatanLevels)];
                $dibaca = $level === 'info' ? rand(0, 1) : 0;

                DB::table('agent_peringatan_log')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'jenis_peringatan' => ['BKD', 'Kalibrasi', 'Akreditasi', 'Dokumen'][rand(0, 3)],
                    'tingkat' => $level,
                    'judul' => 'Peringatan ' . ucfirst($level) . ' #' . ($i + 1),
                    'pesan' => 'Peringatan otomatis dari sistem AI.',
                    'dibaca_pada' => $dibaca ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($prodis as $prodi) {
            foreach ($dosens->where('prodi_id', $prodi->id)->take(5) as $dosen) {
                for ($i = 0; $i < rand(2, 5); $i++) {
                    DB::table('agent_verifikasi_hasil')->insert([
                        'dosen_id' => $dosen->id,
                        'prodi_id' => $prodi->id,
                        'jenis_dokumen' => ['Portofolio', 'BKD', 'Publikasi', 'Penelitian'][rand(0, 3)],
                        'nama_dokumen' => 'Dokumen verifikasi #' . ($i + 1),
                        'status' => $verifikasiStatuses[array_rand($verifikasiStatuses)],
                        'tingkat_kepercayaan' => rand(60, 99),
                        'catatan' => rand(0, 1) ? 'Dokumen terverifikasi dengan baik.' : null,
                        'created_at' => now()->subDays(rand(1, 90)),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        foreach ($prodis as $prodi) {
            foreach ($periodes->take(2) as $periode) {
                DB::table('agent_generator_history')->insert([
                    'prodi_id' => $prodi->id,
                    'periode_id' => $periode->id,
                    'jenis_dokumen' => $docTypes[array_rand($docTypes)],
                    'status' => ['completed', 'completed', 'failed'][rand(0, 2)],
                    'prompt_text' => 'Generate ' . $docTypes[array_rand($docTypes)] . ' untuk prodi ' . $prodi->nama_prodi,
                    'hasil_text' => 'Dokumen telah berhasil digenerate oleh AI Agent.',
                    'file_path' => '/storage/generated/' . Str::random(20) . '.docx',
                    'generated_at' => now()->subDays(rand(1, 30)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('integrasi_log_sinkron')->insert([
            'sumber' => 'PDDIKTI',
            'jenis_data' => 'dosen',
            'status' => 'success',
            'jumlah_ditarik' => $dosens->count(),
            'jumlah_konflik' => rand(0, 5),
            'waktu_mulai' => now()->subHours(rand(1, 24)),
            'waktu_selesai' => now()->subHours(rand(0, 23)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('integrasi_log_sinkron')->insert([
            'sumber' => 'SINTA',
            'jenis_data' => 'publikasi',
            'status' => 'success',
            'jumlah_ditarik' => rand(50, 200),
            'jumlah_konflik' => rand(0, 10),
            'waktu_mulai' => now()->subHours(rand(1, 48)),
            'waktu_selesai' => now()->subHours(rand(0, 47)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Agent history seeded successfully.');
    }
}