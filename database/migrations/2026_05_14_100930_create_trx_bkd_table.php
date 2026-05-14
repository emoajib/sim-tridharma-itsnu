<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_bkd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->decimal('total_sks_mengajar', 8)->default(0);
            $table->decimal('total_sks_penelitian', 8)->default(0);
            $table->decimal('total_sks_pkm', 8)->default(0);
            $table->decimal('total_sks_penunjang', 8)->default(0);
            $table->decimal('total_sks', 8)->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('catatan')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_bkd');
    }
};
