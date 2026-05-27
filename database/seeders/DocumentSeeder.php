<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use App\Models\DokumenBukti;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::first();

        DokumenBukti::firstOrCreate(
            ['nama_dokumen' => 'Sertifikat Akreditasi 2024', 'prodi_id' => $prodi->id],
            [
                'file_path' => 'storage/docs/sertifikat.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1024,
                'hash' => md5('sertifikat.pdf'),
                'is_verified' => false,
            ]
        );

        DokumenBukti::firstOrCreate(
            ['nama_dokumen' => 'SK Pendirian Prodi', 'prodi_id' => $prodi->id],
            [
                'file_path' => 'storage/docs/sk_pendirian.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 2048,
                'hash' => md5('sk_pendirian.pdf'),
                'is_verified' => false,
            ]
        );

        echo "Document dummy berhasil dibuat.\n";
    }
}
