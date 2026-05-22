<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiPortofolioSeeder extends Seeder
{
    public function run(): void
    {
        $dosens = DB::table('m_dosen')->whereNotNull('prodi_id')->get();
        $prodis = DB::table('m_prodi')->get();
        $mk = DB::table('m_mata_kuliah')->get();
        $periodes = DB::table('m_periode_akademik')->get();
        $mahasiswas = DB::table('m_mahasiswa')->get();

        if ($dosens->isEmpty() || $prodis->isEmpty() || $periodes->isEmpty() || $mahasiswas->isEmpty()) {
            $this->command->warn('Master data (dosen/prodi/periode/mahasiswa) kosong. Lewati seeding portofolio.');

            return;
        }

        foreach ($dosens as $dosen) {
            foreach ($periodes->take(2) as $periode) {
                $mkId = $mk->where('prodi_id', $dosen->prodi_id)->first()?->id;

                if (! $mkId) {
                    continue;
                }

                DB::table('trx_kegiatan_pendidikan')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'mata_kuliah_id' => $mkId,
                    'nama_kegiatan' => 'Pengajaran Mata Kuliah',
                    'jenis_kegiatan' => 'Pengajaran',
                    'sks' => rand(3, 6),
                    'jumlah_mahasiswa' => rand(20, 50),
                    'jumlah_pertemuan' => rand(12, 16),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_penelitian')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'judul_penelitian' => 'Penelitian Dosen '.$dosen->nama_depan.' '.$dosen->nama_belakang,
                    'jenis_penelitian' => 'Penelitian Mandiri',
                    'sumber_dana' => ['BRIN', 'LPPM', 'Mandiri'][rand(0, 2)],
                    'jumlah_dana' => rand(10000000, 50000000),
                    'tahun_pelaksanaan' => (string) now()->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_publikasi')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'judul_publikasi' => 'Publikasi Ilmiah: '.Str::random(30),
                    'jenis_publikasi' => ['Jurnal Nasional', 'Jurnal Internasional', 'Prosiding'][rand(0, 2)],
                    'tingkat' => ['Nasional', 'Internasional'][rand(0, 1)],
                    'tahun' => (string) now()->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (rand(0, 1)) {
                    DB::table('trx_pkm')->insert([
                        'dosen_id' => $dosen->id,
                        'prodi_id' => $dosen->prodi_id,
                        'periode_id' => $periode->id,
                        'judul_pkm' => 'Pengabdian Masyarakat: '.Str::random(25),
                        'jenis_pkm' => 'Penyuluhan',
                        'sumber_dana' => ['LPPM', 'Mandiri'][rand(0, 1)],
                        'jumlah_dana' => rand(5000000, 20000000),
                        'tahun_pelaksanaan' => (string) now()->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('trx_penunjang')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'nama_kegiatan' => ['Seminar', 'Workshop', 'Pelatihan', 'Organisasi'][rand(0, 3)],
                    'jenis_kegiatan' => 'Penunjang Akademik',
                    'tingkat' => ['Lokal', 'Nasional', 'Internasional'][rand(0, 2)],
                    'tahun' => (string) now()->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_bkd')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'total_sks_mengajar' => rand(8, 12),
                    'total_sks_penelitian' => rand(2, 6),
                    'total_sks_pkm' => rand(1, 4),
                    'total_sks_penunjang' => rand(1, 3),
                    'total_sks' => rand(12, 16),
                    'status' => 'submitted',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_keuangan')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'jenis_dana' => ['Hibah', 'Kontrak', 'Lainnya'][rand(0, 2)],
                    'sumber_dana' => ['PNBP', 'LPDP', 'Lainnya'][rand(0, 2)],
                    'jumlah' => rand(10000000, 100000000),
                    'tahun' => (string) now()->year,
                    'status' => 'verified',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $mhsProdi = $mahasiswas->where('prodi_id', $dosen->prodi_id);
            if ($mhsProdi->isNotEmpty()) {
                for ($i = 0; $i < rand(2, 5); $i++) {
                    $periode = $periodes->random();
                    $mhs = $mhsProdi->random();
                    DB::table('trx_mahasiswa_bimbingan')->insert([
                        'dosen_id' => $dosen->id,
                        'mahasiswa_id' => $mhs->id,
                        'prodi_id' => $dosen->prodi_id,
                        'periode_id' => $periode->id,
                        'jenis_bimbingan' => ['Tugas Akhir', 'Skripsi', 'Tesis', 'PKL'][rand(0, 3)],
                        'judul' => 'Judul Bimbingan '.Str::random(20),
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Transaksi portofolio seeded successfully.');
    }
}
