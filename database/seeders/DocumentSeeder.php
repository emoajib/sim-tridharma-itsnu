<?php

namespace Database\Seeders;

use App\Models\DokumenBukti;
use App\Models\Prodi;
use App\Models\Dosen;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::first();
        
        DokumenBukti::create([
            'prodi_id' => $prodi->id,
            'nama_dokumen' => 'Sertifikat Akreditasi 2024',
            'file_path' => 'storage/docs/sertifikat.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024,
            'hash' => md5('sertifikat.pdf'),
            'is_verified' => false,
        ]);

        DokumenBukti::create([
            'prodi_id' => $prodi->id,
            'nama_dokumen' => 'SK Pendirian Prodi',
            'file_path' => 'storage/docs/sk_pendirian.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 2048,
            'hash' => md5('sk_pendirian.pdf'),
            'is_verified' => false,
        ]);

        echo "Document dummy berhasil dibuat.\n";
    }
}
