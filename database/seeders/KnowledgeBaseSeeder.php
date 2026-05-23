<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\AI\EmbeddingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Vetted by AI - Manual Review Required by Senior Engineer/Manager
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $embeddingService = app(EmbeddingService::class);

        // 1. Create Category
        $category = KnowledgeBaseCategory::firstOrCreate(
            ['nama' => 'Pedoman Akreditasi'],
            [
                'singkatan' => 'PEDOMAN',
                'deskripsi' => 'Kumpulan dokumen aturan, biaya, dan prosedur akreditasi dari BAN-PT dan LAM.'
            ]
        );

        $documents = [
            [
                'judul' => 'Pedoman Akreditasi BAN-PT & Permendikbudristek 53/2023 (D3 Kriya Batik)',
                'sumber' => 'https://www.banpt.or.id & https://jdih.kemdikbud.go.id',
                'content' => "Berdasarkan Permendikbudristek Nomor 53 Tahun 2023 tentang Penjaminan Mutu Pendidikan Tinggi, sistem akreditasi di Indonesia mengalami transformasi besar. Status akreditasi kini disederhanakan menjadi Terakreditasi dan Tidak Terakreditasi. Peringkat Unggul tetap dipertahankan namun bersifat sukarela. Untuk prodi D3 Kriya Batik di ITSNU Pekalongan yang bernaung di bawah BAN-PT, berlaku mekanisme perpanjangan otomatis (Automasi) setiap 5 tahun. Perpanjangan ini dilakukan melalui sistem SAPTO berdasarkan data di PDDIKTI. Jika data PDDIKTI menunjukkan mutu yang konsisten, akreditasi diperpanjang tanpa asesmen lapangan. Namun, BAN-PT melakukan Pemantauan dan Evaluasi Peringkat Akreditasi (PEPA). Jika ditemukan pelanggaran atau penurunan mutu (seperti rasio dosen-mahasiswa yang buruk), BAN-PT memberikan waktu perbaikan 6 bulan. Jika tidak diperbaiki, status akreditasi dapat diturunkan atau dicabut. Lulusan dari prodi yang tidak terakreditasi tidak dapat menggunakan ijazahnya untuk melamar CPNS."
            ],
            [
                'judul' => 'Pedoman Akreditasi LAM INFOKOM (S1 Teknologi Informasi & Informatika)',
                'sumber' => 'https://laminfokom.or.id',
                'content' => "LAM INFOKOM mengelola akreditasi untuk program studi S1 Teknologi Informasi dan S1 Informatika di ITSNU Pekalongan. Berdasarkan aturan terbaru 2024, biaya akreditasi program studi ditetapkan sebesar Rp53.000.000. LAM INFOKOM sangat tegas terhadap integritas dokumen; berdasarkan Peraturan LAM INFOKOM No. 01/2024, ditemukan kemiripan dokumen atau plagiasi dapat dikenakan sanksi biaya tambahan atau pembatalan proses. Penilaian saat ini menggunakan Instrumen Versi 2.1 yang mencakup 6 kriteria: Budaya Mutu, Relevansi Pendidikan, Relevansi Penelitian, Relevansi Pengabdian kepada Masyarakat, Akuntabilitas, dan Diferensiasi Misi. Seluruh proses pendaftaran dan unggah dokumen dilakukan melalui portal SALAM INFOKOM. Prodi disarankan memastikan publikasi dosen di jurnal bereputasi (Sinta 2 atau Scopus) mencapai minimal 60% untuk skor maksimal."
            ],
            [
                'judul' => 'Pedoman Akreditasi LAM Teknik (S1 Teknik Industri)',
                'sumber' => 'https://lamteknik.or.id',
                'content' => "Program studi S1 Teknik Industri di ITSNU Pekalongan bernaung di bawah LAM Teknik. Biaya akreditasi reguler tahun 2024 adalah Rp53.000.000 per usulan. Pembayaran dilakukan melalui sistem SAKTI (Sistem Akreditasi Teknik Indonesia). Meskipun LAM Teknik bukan institusi PKP (tidak ada PPN), pihak perguruan tinggi diperbolehkan memotong PPh Pasal 23 sebesar 2% mandiri dengan kewajiban mengunggah bukti potong ke sistem. Instrumen akreditasi telah disesuaikan dengan Permendikbudristek 53/2023, yang menitikberatkan pada Laporan Evaluasi Diri (LED) dan Laporan Kinerja Program Studi (LKPS). Evaluasi mencakup pemenuhan standar nasional pendidikan tinggi di bidang keteknikan. Perpanjangan akreditasi dapat dilakukan secara otomatis jika data di PDDIKTI memenuhi ambang batas yang ditetapkan oleh LAM Teknik."
            ],
            [
                'judul' => 'Pedoman Akreditasi LAMEMBA (S1 Bisnis Digital & D3 Akuntansi/Perkantoran)',
                'sumber' => 'https://lamemba.or.id',
                'content' => "Program studi S1 Bisnis Digital, D3 Akuntansi, dan D3 Administrasi Perkantoran di ITSNU Pekalongan mengikuti proses akreditasi di LAMEMBA. Prosedur dilakukan secara daring melalui sistem LEXA (LAMEMBA Excellence in Accreditation). Biaya akreditasi standar adalah Rp53.000.000, yang dibayarkan dalam dua tahap: Tahap Asesmen Kecukupan (AK) sebesar Rp23.300.000 dan Tahap Asesmen Lapangan (AL) sebesar Rp29.700.000. Dengan aturan pajak PPN terbaru 12%, total biaya bisa mencapai sekitar Rp57.770.000 untuk jalur instrumen Unggul. LAMEMBA menggunakan pendekatan penilaian retrospektif (kinerja masa lalu) dan prospektif (rencana strategis masa depan).UPPS wajib memperkuat Sistem Penjaminan Mutu Internal (SPMI) agar sinkron dengan standar akreditasi LAMEMBA."
            ],
            [
                'judul' => 'Pedoman Akreditasi LAMSAMA (S1 Fisika)',
                'sumber' => 'https://lamsama.or.id',
                'content' => "S1 Fisika di ITSNU Pekalongan bernaung di bawah LAMSAMA. Biaya akreditasi ditetapkan sebesar Rp57.500.000. LAMSAMA menyediakan skema cicilan: pembayaran awal (uang muka) sebesar Rp5.000.000 saat pengajuan akun di portal SALAM, dan pelunasan sisanya sebelum finalisasi dokumen. Tahun 2024 merupakan masa transisi; Instrumen IAPS 1.0 masih diterima hingga 21 Maret 2025, namun mulai 1 Juli 2025, LAMSAMA wajib menggunakan IAPS 3.0 yang lebih terintegrasi dengan data PDDIKTI. Penilaian LAMSAMA sangat memperhatikan kualitas luaran penelitian dan publikasi ilmiah mahasiswa di bidang sains alam dan ilmu formal. UPPS disarankan melakukan simulasi skor secara mandiri menggunakan matriks penilaian LAMSAMA sebelum melakukan submit di portal."
            ]
        ];

        foreach ($documents as $docData) {
            $this->command->info("Memproses dokumen: {$docData['judul']}...");

            $document = KnowledgeBaseDocument::create([
                'category_id' => $category->id,
                'judul' => $docData['judul'],
                'sumber' => $docData['sumber'],
                'file_path' => 'seeder-manual',
                'file_size' => strlen($docData['content']),
                'status' => 'active',
            ]);

            // Split content into chunks (by sentences or fixed length)
            // For seeder, we'll split by sentences or paragraphs
            $paragraphs = explode("\n", str_replace("\r", "", $docData['content']));
            
            foreach ($paragraphs as $index => $content) {
                if (empty(trim($content))) continue;

                $this->command->info("  - Membuat embedding untuk chunk index {$index}...");
                
                try {
                    $embedding = $embeddingService->embedText($content);
                    
                    KnowledgeBaseChunk::create([
                        'document_id' => $document->id,
                        'chunk_index' => $index,
                        'content' => $content,
                        'embedding' => $embedding,
                    ]);
                } catch (\Exception $e) {
                    $this->command->error("  - Gagal embedding: " . $e->getMessage());
                    // Fallback create without embedding if service is down, but we want it to work
                    KnowledgeBaseChunk::create([
                        'document_id' => $document->id,
                        'chunk_index' => $index,
                        'content' => $content,
                        'embedding' => null,
                    ]);
                }
            }
        }

        $this->command->info("Seeder KnowledgeBase selesai!");
    }
}
