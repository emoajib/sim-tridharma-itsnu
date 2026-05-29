<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_skpi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('m_mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->enum('jenis_kegiatan', ['Organisasi', 'Kepanitiaan', 'Prestasi', 'Sertifikasi', 'Lainnya']);
            $table->string('nama_kegiatan', 255);
            $table->enum('tingkat', ['Lokal/Wilayah', 'Nasional', 'Internasional'])->nullable();
            $table->string('peran', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('jam_kompen', 6, 2)->default(0);
            $table->decimal('poin_skpi', 6, 2)->default(0);
            $table->string('file_sertifikat')->nullable();
            $table->enum('status_verifikasi', ['DRAFT', 'SUBMITTED', 'VERIFIED'])->default('DRAFT');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['mahasiswa_id', 'nama_kegiatan', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_skpi');
    }
};
