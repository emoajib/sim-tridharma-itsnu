<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_kerjasama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mitra_id')->constrained('m_mitra')->cascadeOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->string('jenis_kerjasama', 50);
            $table->string('nomor_mou', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('status', 30)->default('aktif');
            $table->string('file_dokumen', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_kerjasama');
    }
};
