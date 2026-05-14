<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_skor_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->decimal('skor_total', 5);
            $table->decimal('skor_prediksi', 5)->nullable();
            $table->decimal('confidence_interval', 5)->nullable();
            $table->decimal('probabilitas_unggul', 5)->nullable();
            $table->decimal('probabilitas_baik_sekali', 5)->nullable();
            $table->decimal('probabilitas_baik', 5)->nullable();
            $table->string('sumber_data', 50)->default('manual');
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_skor_akreditasi');
    }
};
