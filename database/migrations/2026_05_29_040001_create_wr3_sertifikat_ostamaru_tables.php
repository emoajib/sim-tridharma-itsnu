<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_sertifikat_ostamaru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('m_mahasiswa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->restrictOnDelete();
            $table->enum('jenis_sertifikat', ['OSTAMARU', 'PK2', 'Diksar', 'Lainnya']);
            $table->string('nomor_sertifikat', 100)->unique();
            $table->date('tanggal_terbit');
            $table->string('file_sertifikat');
            $table->boolean('is_downloadable')->default(true);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_sertifikat_ostamaru');
    }
};
