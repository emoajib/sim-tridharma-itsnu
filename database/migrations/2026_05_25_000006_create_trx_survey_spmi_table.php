<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_survey_spmi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->enum('responden_type', ['mahasiswa', 'dosen', 'alumni', 'pengguna_lulusan']);
            $table->json('responses');
            $table->decimal('skor_rata_rata', 5, 2)->nullable();
            $table->string('token', 64)->unique()->nullable();
            $table->timestamp('diisi_at')->nullable();
            $table->timestamps();

            $table->index(['periode_id', 'responden_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_survey_spmi');
    }
};
