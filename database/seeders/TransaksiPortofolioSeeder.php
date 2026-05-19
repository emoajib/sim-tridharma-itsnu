<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiPortofolioSeeder extends Seeder
{
    public function run(): void
    {
        $dosens = DB::table('m_dosen')->get();
        $prodis = DB::table('m_prodi')->get();
        $mk = DB::table('m_mata_kuliah')->get();
        $periodes = DB::table('m_periode_akademik')->get();

        if ($dosens->isEmpty() || $prodis->isEmpty() || $periodes->isEmpty()) {
            $this->command->warn('Master data (dosen/prodi/periode) kosong. Lewati seeding portofolio.');
            return;
        }

        foreach ($dosens as $dosen) {
            foreach ($periodes->take(2) as $periode) {
                DB::table('trx_kegitan_pendidikan')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'mata_kuliah_id' => $mk->where('prodi_id', $dosen->prodi_id)->first()?->id,
                    'nama_kegitan' => 'Pengajaran Mata Kuliah',
                    'jenis_kegitan' => 'Pengajaran',
                    'sks' => rand(3, 6),
                    'jumlah_mahasiswa' => rand(20, 50),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_penelitian')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'judul_penelitian' => 'Penelitian Dosen ' . $dosen->nama_depan . ' ' . $dosen->nama_belakang,
                    'sumber_dana' => ['BRIN', 'LPPM', 'Mandiri'][rand(0, 2)],
                    'jumlah_dana' => rand(10000000, 50000000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_publikasi')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'judul_publikasi' => 'Publikasi Ilmiah: ' . Str::random(30),
                    'jenis_publikasi' => ['Jurnal Nasional', 'Jurnal Internasional', 'Prosiding'][rand(0, 2)],
                    'tahun' => now()->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (rand(0, 1)) {
                    DB::table('trx_pkm')->insert([
                        'dosen_id' => $dosen->id,
                        'prodi_id' => $dosen->prodi_id,
                        'periode_id' => $periode->id,
                        'judul_pkm' => 'Pengabdian Masyarakat: ' . Str::random(25),
                        'sumber_dana' => ['LPPM', 'Mandiri'][rand(0, 1)],
                        'jumlah_dana' => rand(5000000, 20000000),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('trx_penunjang')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'nama_kegitan' => ['Seminar', 'Workshop', 'Pelatihan', 'Organisasi'][rand(0, 3)],
                    'tingkat' => ['Lokal', 'Nasional', 'Internasional'][rand(0, 2)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('trx_bkd')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'sks_pendidikan' => rand(8, 12),
                    'sks_penelitian' => rand(2, 6),
                    'sks_pkm' => rand(1, 4),
                    'sks_penunjang' => rand(1, 3),
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            for ($i = 0; $i < rand(2, 5); $i++) {
                $periode = $periodes->random();
                DB::table('trx_mahasiswa_bimbingan')->insert([
                    'dosen_id' => $dosen->id,
                    'prodi_id' => $dosen->prodi_id,
                    'periode_id' => $periode->id,
                    'mahasiswa_nim' => 'MHS' . rand(100000, 999999),
                    'mahasiswa_nama' => 'Mahasiswa Bimbingan ' . Str::random(20),
                    'jenis_bimbingan' => ['Tugas Akhir', 'Skripsi', 'Tesis', 'PKL'][rand(0, 3)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Transaksi portofolio seeded successfully.');
    }
}