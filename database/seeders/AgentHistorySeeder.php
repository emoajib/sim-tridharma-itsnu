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
        $docs = DB::table('doc_bukti')->get();
        $indikators = DB::table('m_indikator_akreditasi')->get();

        if ($prodis->isEmpty()) {
            $this->command->warn('Prodi kosong, lewati seeding agent history.');
            return;
        }

        $agents = ['prediksi', 'rekomendasi', 'peringatan', 'verifikasi', 'generator', 'integrasi'];
        $agentStatuses = ['success', 'success', 'success', 'failed'];

        foreach ($agents as $agent) {
            for ($i = 0; $i < rand(5, 10); $i++) {
                $startTime = Carbon::now()->subMinutes(rand(1, 1000));
                $duration = rand(500, 5000);

                DB::table('agent_execution_log')->insert([
                    'agent_name' => $agent,
                    'status' => $agentStatuses[array_rand($agentStatuses)],
                    'started_at' => $startTime,
                    'finished_at' => (clone $startTime)->addMilliseconds($duration),
                    'duration_ms' => $duration,
                    'input_data' => json_encode(['prodi_id' => $prodis->random()->id]),
                    'output_data' => json_encode(['result' => 'Execution successful']),
                    'triggered_by' => 'system',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($prodis as $prodi) {
            foreach ($periodes->take(2) as $periode) {
                DB::table('agent_prediction_history')->insert([
                    'prodi_id' => $prodi->id,
                    'periode_id' => $periode->id,
                    'skor_prediksi' => rand(60, 95),
                    'confidence_interval' => rand(85, 98),
                    'probabilitas_unggul' => rand(10, 80) / 100,
                    'probabilitas_baik_sekali' => rand(10, 50) / 100,
                    'probabilitas_baik' => rand(0, 30) / 100,
                    'detail_data' => json_encode(['trend' => 'upward', 'factors' => ['SDM', 'Penelitian']]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($indikators->random(min(5, $indikators->count())) as $indikator) {
                DB::table('agent_rekomendasi_log')->insert([
                    'prodi_id' => $prodi->id,
                    'indikator_id' => $indikator->id,
                    'judul_rekomendasi' => 'Rekomendasi Strategis ' . $indikator->kode_indikator,
                    'deskripsi' => 'Analisis AI menunjukkan perlunya peningkatan pada aspek ini.',
                    'prioritas' => rand(1, 3),
                    'status' => 'baru',
                    'target_capai' => 'Peningkatan 20%',
                    'deadline' => now()->addMonths(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($dosens->random(min(20, $dosens->count())) as $dosen) {
            for ($i = 0; $i < 3; $i++) {
                DB::table('agent_peringatan_log')->insert([
                    'prodi_id' => $dosen->prodi_id,
                    'dosen_id' => $dosen->id,
                    'jenis_peringatan' => 'kinerja',
                    'tingkat' => ['info', 'warning', 'critical'][rand(0, 2)],
                    'pesan' => 'Pesan peringatan otomatis dari AI Agent.',
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($docs->isNotEmpty()) {
            foreach ($docs->random(min(20, $docs->count())) as $doc) {
                DB::table('agent_verifikasi_hasil')->insert([
                    'prodi_id' => $doc->prodi_id,
                    'dosen_id' => $doc->dosen_id,
                    'doc_bukti_id' => $doc->id,
                    'status' => 'verified',
                    'catatan' => 'Terverifikasi otomatis oleh AI.',
                    'tingkat_kepercayaan' => rand(90, 99),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($prodis as $prodi) {
            DB::table('agent_generator_history')->insert([
                'prodi_id' => $prodi->id,
                'jenis_dokumen' => 'LED',
                'judul' => 'Laporan Evaluasi Diri 2026',
                'status' => 'completed',
                'generated_by' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Agent history seeded successfully.');
    }
}