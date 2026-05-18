<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Seeder;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::all()->keyBy('kode_prodi');
        $userDosen = User::where('email', 'dosen@itsnu.ac.id')->first();

        $dosens = [
            [
                'nidn' => '0012345678',
                'nama_depan' => 'Ahmad',
                'nama_belakang' => 'Fauzi',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'S.Kom., M.Kom.',
                'prodi_kode' => 'IF',
                'jabatan_fungsional' => 'Lektor Kepala',
                'pendidikan_terakhir' => 'S3',
                'email' => 'ahmad.fauzi@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345679',
                'nama_depan' => 'Siti',
                'nama_belakang' => 'Nurhayati',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'S.Pd., M.Pd.',
                'prodi_kode' => 'FIS',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S3',
                'email' => 'siti.nurhayati@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345680',
                'nama_depan' => 'Budi',
                'nama_belakang' => 'Santoso',
                'gelar_belakang' => 'S.T., M.T.',
                'prodi_kode' => 'TI',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'email' => 'budi.santoso@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345681',
                'nama_depan' => 'Rina',
                'nama_belakang' => 'Wijaya',
                'gelar_belakang' => 'S.E., M.Si.',
                'prodi_kode' => 'AK',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'email' => 'rina.wijaya@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345682',
                'nama_depan' => 'Doni',
                'nama_belakang' => 'Prasetyo',
                'gelar_belakang' => 'S.Kom., M.Eng.',
                'prodi_kode' => 'IF',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'email' => 'doni.prasetyo@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345683',
                'nama_depan' => 'Dewi',
                'nama_belakang' => 'Lestari',
                'gelar_depan' => 'Dr.',
                'gelar_belakang' => 'S.E., M.M.',
                'prodi_kode' => 'MJ',
                'jabatan_fungsional' => 'Lektor Kepala',
                'pendidikan_terakhir' => 'S3',
                'email' => 'dewi.lestari@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345684',
                'nama_depan' => 'Agus',
                'nama_belakang' => 'Hermawan',
                'gelar_belakang' => 'S.T., M.T.',
                'prodi_kode' => 'TI',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'email' => 'agus.hermawan@itsnu.ac.id',
            ],
            [
                'nidn' => '0012345685',
                'nama_depan' => 'Fitri',
                'nama_belakang' => 'Handayani',
                'gelar_belakang' => 'S.Si., M.Pd.',
                'prodi_kode' => 'FIS',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'email' => 'fitri.handayani@itsnu.ac.id',
            ],
        ];

        foreach ($dosens as $data) {
            $prodiModel = $prodi->get($data['prodi_kode']);
            $dosen = Dosen::firstOrCreate(
                ['nidn' => $data['nidn']],
                [
                    'nama_depan' => $data['nama_depan'],
                    'nama_belakang' => $data['nama_belakang'],
                    'gelar_depan' => $data['gelar_depan'] ?? null,
                    'gelar_belakang' => $data['gelar_belakang'] ?? null,
                    'prodi_id' => $prodiModel?->id,
                    'jabatan_fungsional' => $data['jabatan_fungsional'],
                    'pendidikan_terakhir' => $data['pendidikan_terakhir'],
                    'email' => $data['email'],
                    'status_aktivitas' => 'aktif',
                    'is_active' => true,
                ]
            );
        }

        $count = Dosen::count();
        echo "✅ {$count} data dosen berhasil dibuat\n";
    }
}
