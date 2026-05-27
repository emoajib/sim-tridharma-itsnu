<?php

// Idempotent: safe to re-run

namespace Database\Seeders;

use App\Models\KnowledgeBaseCategory;
use Illuminate\Database\Seeder;

class KnowledgeBaseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama' => 'Badan Akreditasi Nasional Perguruan Tinggi', 'singkatan' => 'BAN-PT', 'deskripsi' => 'Dokumen kebijakan dan instrumen BAN-PT'],
            ['nama' => 'LAM INFOKOM', 'singkatan' => 'LAM INFOKOM', 'deskripsi' => 'Dokumen kebijakan dan instrumen LAM INFOKOM'],
            ['nama' => 'LAMEMBA', 'singkatan' => 'LAMEMBA', 'deskripsi' => 'Dokumen kebijakan dan instrumen LAMEMBA'],
            ['nama' => 'LAM Teknik', 'singkatan' => 'LAM Teknik', 'deskripsi' => 'Dokumen kebijakan dan instrumen LAM Teknik'],
            ['nama' => 'LAMSAMA', 'singkatan' => 'LAMSAMA', 'deskripsi' => 'Dokumen kebijakan dan instrumen LAMSAMA'],
            ['nama' => 'Kemendikbudristek', 'singkatan' => 'Kemendikbud', 'deskripsi' => 'Peraturan dan kebijakan Kemendikbudristek'],
            ['nama' => 'Umum', 'singkatan' => 'Umum', 'deskripsi' => 'Dokumen umum lainnya'],
        ];

        foreach ($categories as $cat) {
            KnowledgeBaseCategory::updateOrCreate(
                ['singkatan' => $cat['singkatan']],
                $cat
            );
        }
    }
}
