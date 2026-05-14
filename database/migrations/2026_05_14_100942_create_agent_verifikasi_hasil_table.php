<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_verifikasi_hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('doc_bukti_id')->nullable()->constrained('doc_bukti')->cascadeOnDelete();
            $table->foreignId('indikator_id')->nullable()->constrained('m_indikator_akreditasi')->cascadeOnDelete();
            $table->string('status', 20);
            $table->text('catatan')->nullable();
            $table->decimal('tingkat_kepercayaan', 5)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_verifikasi_hasil');
    }
};
