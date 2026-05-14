<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_audit_mutu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->string('judul_audit', 200);
            $table->date('tanggal_audit');
            $table->string('auditor', 200)->nullable();
            $table->text('temuan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->string('status', 30)->default('open');
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_audit_mutu');
    }
};
