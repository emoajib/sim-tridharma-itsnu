<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_penunjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->string('nama_kegiatan', 200);
            $table->string('jenis_kegiatan', 50);
            $table->string('tingkat', 30)->nullable();
            $table->string('peran', 100)->nullable();
            $table->string('tahun', 10);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_penunjang');
    }
};
