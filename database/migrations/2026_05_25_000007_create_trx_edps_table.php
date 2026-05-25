<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_edps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->foreignId('standar_mutu_id')->constrained('m_standar_mutu')->cascadeOnDelete();
            $table->decimal('target', 5, 2);
            $table->decimal('capaian', 5, 2)->nullable();
            $table->text('analisis')->nullable();
            $table->string('bukti_file', 255)->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->timestamps();

            $table->unique(['prodi_id', 'periode_id', 'standar_mutu_id']);
            $table->index(['prodi_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_edps');
    }
};
