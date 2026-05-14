<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_prodi_keahlian_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_keahlian_id')->constrained('m_prodi_keahlian')->cascadeOnDelete();
            $table->boolean('is_utama')->default(false);
            $table->unique(['dosen_id', 'prodi_keahlian_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_prodi_keahlian_dosen');
    }
};
