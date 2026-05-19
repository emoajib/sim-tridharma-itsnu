<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = DB::table('m_prodi')->get();

        if ($prodis->isEmpty()) {
            $this->command->warn('Prodi kosong, lewati seeding alumni.');
            return;
        }

        $alumniData = [];
        $tahunLulus = ['2020', '2021', '2022', '2023', '2024'];

        foreach ($prodis as $prodi) {
            $jumlahAlumni = rand(5, 10);
            for ($i = 0; $i < $jumlahAlumni; $i++) {
                $tahun = $tahunLulus[array_rand($tahunLulus)];
                $nim = $prodi->kode_prodi . str_pad(rand(1000, 9999), 4, '0');

                $alumniId = DB::table('m_alumni')->insertGetId([
                    'prodi_id' => $prodi->id,
                    'nim' => $nim,
                    'nama' => 'Alumni ' . Str::random(25),
                    'tahun_lulus' => $tahun,
                    'masa_tunggu' => rand(1, 12),
                    'gaji_pertama' => rand(3000000, 15000000),
                    'pekerjaan' => ['PT', 'UMKM', 'Wiraswasta', 'PTN', 'PNS'][rand(0, 4)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $alumniData[] = $alumniId;
            }
        }

        $periodes = DB::table('m_periode_akademik')->get();
        if (!empty($alumniData) && $periodes->isNotEmpty()) {
            foreach ($alumniData as $alumniId) {
                $kuisioners = DB::table('m_kuisioner_tracer')
                    ->get();

                if ($kuisioners->isNotEmpty()) {
                    foreach ($kuisioners as $kuisioner) {
                        DB::table('trx_tracer_jawaban')->insert([
                            'alumni_id' => $alumniId,
                            'kuisioner_id' => $kuisioner->id,
                            'jawaban' => json_encode([
                                'q1' => rand(1, 5),
                                'q2' => 'Sudah bekerja',
                                'q3' => rand(3000000, 10000000),
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Alumni dan tracer jawaban seeded successfully.');
    }
}