<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_pemenuhan_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('m_indikator_akreditasi')->cascadeOnDelete();
            $table->text('capaian')->nullable();
            $table->decimal('nilai', 5)->nullable();
            $table->string('status', 20)->default('merah');
            $table->text('catatan')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unique(['prodi_id', 'periode_id', 'indikator_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_pemenuhan_indikator');
    }
};
