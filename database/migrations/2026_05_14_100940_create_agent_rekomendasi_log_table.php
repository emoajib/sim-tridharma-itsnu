<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_rekomendasi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('indikator_id')->nullable()->constrained('m_indikator_akreditasi')->cascadeOnDelete();
            $table->string('judul_rekomendasi', 200);
            $table->text('deskripsi');
            $table->string('prioritas', 10);
            $table->string('status', 30)->default('baru');
            $table->text('target_capai')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_rekomendasi_log');
    }
};
