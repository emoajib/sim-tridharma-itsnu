<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = DB::table('m_prodi')->get();
        $periodes = DB::table('m_periode_akademik')->get();

        if ($prodis->isEmpty()) {
            $this->command->warn('Prodi kosong, lewati seeding master data.');

            return;
        }

        $mkNames = [
            'Matematika Dasar', 'Fisika Dasar', 'Kimia Dasar', 'Biologi Dasar',
            'Kalkulus I', 'Kalkulus II', 'Aljabar Linear', 'Statistika',
            'Pemrograman Dasar', 'Struktur Data', 'Basis Data', 'Jaringan Komputer',
            'Rekayasa Perangkat Lunak', 'Kecerdasan Buatan', 'Pembelajaran Mesin',
            'Etika Profesi', 'Bahasa Indonesia', 'Bahasa Inggris', 'Kewarganegaraan',
            'Pengantar Akreditasi', 'Manajemen Proyek', 'Riset Operasional',
        ];

        $mkData = [];
        foreach ($prodis as $prodi) {
            $mkCount = rand(8, 15);
            $selectedMk = array_rand(array_flip($mkNames), min($mkCount, count($mkNames)));

            foreach ($selectedMk as $index => $mkName) {
                $existingMk = DB::table('m_mata_kuliah')
                    ->where('kode_mk', $prodi->kode_prodi.str_pad($index + 1, 3, '0', STR_PAD_LEFT))
                    ->first();

                if ($existingMk) {
                    $mkId = $existingMk->id;
                } else {
                    $mkId = DB::table('m_mata_kuliah')->insertGetId([
                        'prodi_id' => $prodi->id,
                        'kode_mk' => $prodi->kode_prodi.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'nama_mk' => $mkName,
                        'sks' => rand(2, 4),
                        'semester' => ($index % 8) + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $mkData[$prodi->id][] = $mkId;
            }
        }

        foreach ($prodis as $prodi) {
            $kurikulumNama = ['Kurikulum 2020', 'Kurikulum 2022', 'Kurikulum 2024'][rand(0, 2)];
            $kurikulumId = DB::table('m_kurikulum')->insertGetId([
                'prodi_id' => $prodi->id,
                'nama_kurikulum' => $kurikulumNama,
                'tahun_berlaku' => (string) (now()->year - rand(0, 4)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (isset($mkData[$prodi->id])) {
                foreach ($mkData[$prodi->id] as $mkId) {
                    DB::table('m_mk_kurikulum')->insert([
                        'kurikulum_id' => $kurikulumId,
                        'mata_kuliah_id' => $mkId,
                        'semester_rekomendasi' => rand(1, 8),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $cplJenis = ['Sikap', 'Pengetahuan', 'Keterampilan Umum', 'Keterampilan Khusus'];
            for ($i = 0; $i < rand(8, 12); $i++) {
                DB::table('m_cpl')->insert([
                    'prodi_id' => $prodi->id,
                    'kode_cpl' => 'CPL'.($i + 1),
                    'deskripsi' => 'CPL '.($i + 1).': '.Str::random(40),
                    'jenis' => $cplJenis[array_rand($cplJenis)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $cplPerProdi = [];
        foreach ($prodis as $prodi) {
            $cpls = DB::table('m_cpl')->where('prodi_id', $prodi->id)->get();
            $mks = DB::table('m_mata_kuliah')->where('prodi_id', $prodi->id)->get();

            foreach ($cpls as $cpl) {
                $mappings = rand(2, 5);
                $selectedMk = $mks->random(min($mappings, $mks->count()));
                foreach ($selectedMk as $mk) {
                    DB::table('m_cpl_mk')->insert([
                        'cpl_id' => $cpl->id,
                        'mata_kuliah_id' => $mk->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            for ($i = 0; $i < rand(2, 4); $i++) {
                DB::table('m_prodi_keahlian')->insert([
                    'prodi_id' => $prodi->id,
                    'nama_keahlian' => ['Kecerdasan Buatan', 'Rekayasa Perangkat Lunak', 'Data Science', 'Keamanan Siber', 'IoT'][rand(0, 4)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($periodes->isNotEmpty() && ! empty($mkData)) {
            foreach ($prodis as $prodi) {
                $mks = DB::table('m_mata_kuliah')->where('prodi_id', $prodi->id)->get();
                $periode = $periodes->first();

                foreach ($mks->take(5) as $mk) {
                    DB::table('m_rps')->insert([
                        'prodi_id' => $prodi->id,
                        'mata_kuliah_id' => $mk->id,
                        'periode_id' => $periode->id,
                        'status' => 'approved',
                        'file_path' => '/storage/rps/'.Str::random(20).'.pdf',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $dosens = DB::table('m_dosen')->get();
        $keahlians = DB::table('m_prodi_keahlian')->get();
        foreach ($dosens as $dosen) {
            $prodiKeahlian = $keahlians->where('prodi_id', $dosen->prodi_id);
            if ($prodiKeahlian->isNotEmpty()) {
                $assigned = $prodiKeahlian->random(min(2, $prodiKeahlian->count()));
                foreach ($assigned as $keahlian) {
                    DB::table('trx_prodi_keahlian_dosen')->insert([
                        'dosen_id' => $dosen->id,
                        'prodi_keahlian_id' => $keahlian->id,
                        'is_utama' => rand(0, 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $mitraJenjang = ['Internasional', 'Nasional', 'Lokal'];
        $mitraJenis = ['Industri', 'Perusahaan', 'Institut', 'Pemerintah', 'NGO'];
        for ($i = 0; $i < rand(15, 30); $i++) {
            $mitraId = DB::table('m_mitra')->insertGetId([
                'nama_mitra' => 'Mitra '.Str::random(20),
                'jenis_mitra' => $mitraJenis[array_rand($mitraJenis)],
                'alamat' => 'Jl. '.Str::random(30),
                'telepon' => '021-'.rand(1000000, 9999999),
                'email' => 'info@mitra'.$i.'.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $prodi = $prodis->random();
            $periode = $periodes->first();
            $tahunMulai = now()->year - rand(0, 3);

            DB::table('m_kerjasama')->insert([
                'mitra_id' => $mitraId,
                'prodi_id' => $prodi->id,
                'jenis_kerjasama' => ['Pendidikan', 'Penelitian', 'PKM', 'Magang'][rand(0, 3)],
                'nomor_mou' => 'MoU/'.$tahunMulai.'/ITSNU/'.rand(100, 999),
                'tanggal_mulai' => $tahunMulai.'-01-01',
                'tanggal_selesai' => ($tahunMulai + 3).'-12-31',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($prodis as $prodi) {
            for ($i = 0; $i < rand(5, 15); $i++) {
                DB::table('m_sarana')->insert([
                    'prodi_id' => $prodi->id,
                    'nama_sarana' => 'Sarana '.($i + 1).': '.Str::random(20),
                    'jenis_sarana' => ['Lab', 'Ruang Kelas', 'Perpustakaan', 'Gedung'][rand(0, 3)],
                    'jumlah' => rand(1, 50),
                    'kondisi' => ['Baik', 'Rusak Ringan', 'Rusak Berat'][rand(0, 2)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Master data (MK, Kurikulum, CPL, RPS, Sarana, Kerjasama) seeded successfully.');
    }
}
