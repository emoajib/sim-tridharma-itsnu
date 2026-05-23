<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Lembaga Akreditasi (LAMSAMA, LAM INFOKOM, BAN-PT, etc.)
        Schema::create('m_lembaga_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lembaga', 100);
            $table->string('singkatan', 20)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Kriteria/Instrumen per Lembaga
        Schema::create('m_instrumen_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('m_lembaga_akreditasi')->cascadeOnDelete();
            $table->string('nama_instrumen'); // misal: IAPS 4.0, IAPT 3.0
            $table->json('matriks_kriteria')->nullable(); // Menyimpan daftar C1-C9 atau Aspek 1-4
            $table->timestamps();
        });

        // 3. Mapping Prodi ke Lembaga (Ploting)
        Schema::table('m_prodi', function (Blueprint $table) {
            $table->foreignId('lembaga_akreditasi_id')->nullable()->after('fakultas_id')->constrained('m_lembaga_akreditasi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('m_prodi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lembaga_akreditasi_id');
        });
        Schema::dropIfExists('m_instrumen_akreditasi');
        Schema::dropIfExists('m_lembaga_akreditasi');
    }
};
