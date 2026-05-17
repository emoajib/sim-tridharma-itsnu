<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aipt_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('aspek'); // Budaya Mutu, Relevansi, Akuntabilitas, Diferensiasi Misi
            $table->string('indikator');
            $table->text('deskripsi')->nullable();
            $table->decimal('target_skor', 5, 2)->default(3.00);
            $table->decimal('skor_saat_ini', 5, 2)->default(0.00);
            $table->string('status')->default('kuning'); // hijau, kuning, merah
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aipt_metrics');
    }
};
