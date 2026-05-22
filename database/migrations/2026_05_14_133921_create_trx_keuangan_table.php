<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('m_periode_akademik')->nullOnDelete();
            $table->string('jenis_dana', 50)->default('penelitian');
            $table->string('sumber_dana', 100)->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('tahun', 10)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_keuangan');
    }
};
