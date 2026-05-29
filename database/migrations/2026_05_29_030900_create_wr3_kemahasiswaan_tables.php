<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Snapshot Data Seleksi PMB (LKPS Tabel 2.a)
        Schema::create('trx_seleksi_pmb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->restrictOnDelete();
            $table->integer('pendaftar')->default(0);
            $table->integer('lulus_seleksi')->default(0);
            $table->integer('daftar_ulang')->default(0);
            $table->integer('maba_reguler')->default(0);
            $table->integer('maba_transfer')->default(0);
            $table->integer('maba_asing_ft')->default(0); // Full-time
            $table->integer('maba_asing_pt')->default(0); // Part-time
            $table->timestamps();
            
            $table->unique(['periode_id', 'prodi_id']);
        });

        // 2. Master Ormawa
        Schema::create('m_ormawa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->enum('kategori', ['BEM', 'DPM', 'HIMA', 'UKM']);
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->restrictOnDelete(); // Null for UKM/BEM Univ
            $table->text('visi_misi')->nullable();
            $table->string('file_ad_art')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Pembina Ormawa (Pivot Dosen -> Ormawa)
        Schema::create('m_pembina_ormawa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('m_ormawa')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->string('sk_pembina')->nullable(); // Dokumen SK
            $table->timestamps();
            
            $table->unique(['ormawa_id', 'dosen_id', 'periode_id'], 'uk_pembina_ormawa');
        });

        // 4. Kategori Prestasi
        Schema::create('m_kategori_prestasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100); // e.g., Akademik, Olahraga, Seni
            $table->enum('jenis', ['Akademik', 'Non-Akademik']);
            $table->timestamps();
        });

        // 5. Prestasi Zero-Trust (LKPS 8.b.1 & 8.b.2)
        Schema::create('trx_prestasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('m_kategori_prestasi')->restrictOnDelete();
            $table->string('nama_kompetisi');
            $table->string('penyelenggara');
            $table->date('tanggal_pelaksanaan');
            $table->enum('tingkat', ['Lokal/Wilayah', 'Nasional', 'Internasional']);
            $table->string('peringkat', 50); // Juara 1, Harapan 1, Finalis
            $table->string('bukti_url')->nullable(); // URL Berita/Pengumuman resmi
            $table->string('file_sertifikat')->nullable();
            $table->enum('status_verifikasi', ['DRAFT', 'SUBMITTED', 'REVISION_REQUESTED', 'VERIFIED'])->default('DRAFT');
            $table->text('catatan_reviewer')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Anggota Prestasi (Mencegah Double Counting)
        Schema::create('trx_prestasi_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestasi_id')->constrained('trx_prestasi')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('m_mahasiswa')->cascadeOnDelete();
            $table->enum('peran', ['Ketua', 'Anggota', 'Peserta']);
            $table->decimal('nominal_reward', 12, 2)->default(0);
            $table->enum('status_reward', ['Belum Diajukan', 'Diajukan', 'Disetujui', 'Cair'])->default('Belum Diajukan');
            $table->timestamps();
            
            $table->unique(['prestasi_id', 'mahasiswa_id']);
        });

        // 7. Pengajuan Kegiatan & Laporan (Workflow Ormawa -> WR3)
        Schema::create('trx_proposal_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('m_ormawa')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->string('judul_kegiatan');
            $table->text('latar_belakang');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('rab_diajukan', 15, 2)->default(0);
            $table->decimal('rab_disetujui', 15, 2)->default(0);
            $table->string('file_proposal');
            $table->string('file_lpj')->nullable();
            $table->enum('status_kegiatan', [
                'Draft', 
                'Review_Pembina', 
                'Review_Fakultas', 
                'Review_WR3', 
                'Approved', 
                'Rejected', 
                'LPJ_Submitted', 
                'LPJ_Approved'
            ])->default('Draft');
            $table->timestamps();
        });

        // 8. Fasilitas Mahasiswa (LKPS Sarpras)
        Schema::create('trx_aset_ormawa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('m_ormawa')->restrictOnDelete();
            $table->string('nama_aset');
            $table->integer('jumlah');
            $table->decimal('luas_ruang_m2', 8, 2)->nullable();
            $table->enum('kondisi', ['Sangat Baik', 'Baik', 'Rusak Ringan', 'Rusak Berat']);
            $table->integer('tahun_perolehan')->nullable();
            $table->timestamps();
        });

        // 9. Fasilitas Internet Institusi
        Schema::create('trx_fasilitas_internet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->integer('bandwidth_total_mbps');
            $table->integer('jumlah_mahasiswa_aktif');
            $table->decimal('rasio_mbps_per_mhs', 8, 4)->nullable(); // Calculated
            $table->integer('jumlah_titik_hotspot')->default(0);
            $table->timestamps();
        });

        // 10. Layanan Kemahasiswaan & Konseling
        Schema::create('trx_layanan_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->enum('jenis_layanan', ['Bimbingan Karir', 'Kewirausahaan', 'Kesehatan', 'Beasiswa']);
            $table->string('nama_program');
            $table->date('tanggal_pelaksanaan');
            $table->integer('jumlah_peserta')->default(0);
            $table->string('file_surat_tugas')->nullable();
            $table->string('file_laporan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_layanan_mahasiswa');
        Schema::dropIfExists('trx_fasilitas_internet');
        Schema::dropIfExists('trx_aset_ormawa');
        Schema::dropIfExists('trx_proposal_kegiatan');
        Schema::dropIfExists('trx_prestasi_member');
        Schema::dropIfExists('trx_prestasi');
        Schema::dropIfExists('m_kategori_prestasi');
        Schema::dropIfExists('m_pembina_ormawa');
        Schema::dropIfExists('m_ormawa');
        Schema::dropIfExists('trx_seleksi_pmb');
    }
};
