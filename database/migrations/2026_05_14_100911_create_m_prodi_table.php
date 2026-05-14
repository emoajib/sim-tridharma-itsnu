<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_prodi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_prodi', 20)->unique();
            $table->string('nama_prodi', 200);
            $table->foreignId('fakultas_id')->constrained('m_fakultas')->cascadeOnDelete();
            $table->string('jenjang', 10);
            $table->string('akreditasi', 50)->nullable();
            $table->string('sk_akreditasi', 100)->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_prodi');
    }
};
