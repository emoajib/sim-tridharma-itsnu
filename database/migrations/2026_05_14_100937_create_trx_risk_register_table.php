<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_risk_register', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->text('nama_risiko');
            $table->string('kategori', 50);
            $table->string('dampak', 50);
            $table->string('probabilitas', 50);
            $table->string('skor_risiko', 20);
            $table->text('mitigasi')->nullable();
            $table->string('status', 30)->default('open');
            $table->string('penanggung_jawab', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_risk_register');
    }
};
