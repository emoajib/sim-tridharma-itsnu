<?php

namespace Database\Seeders;

use App\Models\StandarMutu;
use Illuminate\Database\Seeder;

class StandarMutuSeeder extends Seeder
{
    /**
     * Seed 36 Standar Mutu:
     * - 8 Pendidikan (STD-001 to STD-008)
     * - 8 Penelitian (STD-009 to STD-016)
     * - 8 PKM (STD-017 to STD-024)
     * - 12 Tambahan (STD-025 to STD-036)
     *
     * Referensi: SNDIKTI (Permendikbudristek 53/2023) + SPMI Institusi
     */
    public function run(): void
    {
        $standars = [
            // === PENDIDIKAN (8 Standar) ===
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-001',
                'nama_standar' => 'Standar Kompetensi Lulusan',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-002',
                'nama_standar' => 'Standar Isi Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-003',
                'nama_standar' => 'Standar Proses Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-004',
                'nama_standar' => 'Standar Penilaian Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-005',
                'nama_standar' => 'Standar Dosen dan Tenaga Kependidikan',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-006',
                'nama_standar' => 'Standar Sarana dan Prasarana Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-007',
                'nama_standar' => 'Standar Pengelolaan Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Pendidikan',
                'kode_standar' => 'STD-008',
                'nama_standar' => 'Standar Pembiayaan Pembelajaran',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],

            // === PENELITIAN (8 Standar) ===
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-009',
                'nama_standar' => 'Standar Hasil Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-010',
                'nama_standar' => 'Standar Isi Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-011',
                'nama_standar' => 'Standar Proses Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-012',
                'nama_standar' => 'Standar Penilaian Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-013',
                'nama_standar' => 'Standar Peneliti',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-014',
                'nama_standar' => 'Standar Sarana dan Prasarana Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-015',
                'nama_standar' => 'Standar Pengelolaan Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Penelitian',
                'kode_standar' => 'STD-016',
                'nama_standar' => 'Standar Pendanaan dan Pembiayaan Penelitian',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],

            // === PKM (8 Standar) ===
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-017',
                'nama_standar' => 'Standar Hasil PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-018',
                'nama_standar' => 'Standar Isi PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-019',
                'nama_standar' => 'Standar Proses PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-020',
                'nama_standar' => 'Standar Penilaian PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-021',
                'nama_standar' => 'Standar Pelaksana PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-022',
                'nama_standar' => 'Standar Sarana dan Prasarana PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-023',
                'nama_standar' => 'Standar Pengelolaan PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'PKM',
                'kode_standar' => 'STD-024',
                'nama_standar' => 'Standar Pendanaan dan Pembiayaan PKM',
                'sumber' => 'SNDIKTI',
                'target_nilai' => 3.00,
            ],

            // === TAMBAHAN (12 Standar) ===
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-025',
                'nama_standar' => 'Standar Visi, Misi, Tujuan dan Strategi',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-026',
                'nama_standar' => 'Standar Tata Pamong dan Kerjasama',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-027',
                'nama_standar' => 'Standar Kemahasiswaan',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-028',
                'nama_standar' => 'Standar Sumber Daya Manusia',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-029',
                'nama_standar' => 'Standar Keuangan, Sarana dan Prasarana',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-030',
                'nama_standar' => 'Standar Pendidikan',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-031',
                'nama_standar' => 'Standar Penelitian',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-032',
                'nama_standar' => 'Standar PKM',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-033',
                'nama_standar' => 'Standar Luaran dan Capaian Tridharma',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-034',
                'nama_standar' => 'Standar Jaminan Mutu Internal',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.50,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-035',
                'nama_standar' => 'Standar Sistem Informasi Mutu',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.00,
            ],
            [
                'kategori' => 'Tambahan',
                'kode_standar' => 'STD-036',
                'nama_standar' => 'Standar Audit Mutu Internal',
                'sumber' => 'SPMI Institusi',
                'target_nilai' => 3.50,
            ],
        ];

        foreach ($standars as $data) {
            StandarMutu::create(array_merge($data, [
                'deskripsi' => null,
                'referensi_regulasi' => 'Permendikbudristek 53/2023',
                'is_active' => true,
            ]));
        }
    }
}
