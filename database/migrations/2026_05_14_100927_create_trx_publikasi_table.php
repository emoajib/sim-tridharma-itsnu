<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_publikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->text('judul_publikasi');
            $table->string('jenis_publikasi', 50);
            $table->string('tingkat', 30);
            $table->string('link', 500)->nullable();
            $table->string('tahun', 10);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_publikasi');
    }
};
