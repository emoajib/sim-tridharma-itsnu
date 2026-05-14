<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_sarana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->string('nama_sarana', 200);
            $table->string('jenis_sarana', 50);
            $table->integer('jumlah')->default(1);
            $table->string('kondisi', 30)->default('baik');
            $table->date('tanggal_kalibrasi')->nullable();
            $table->date('tanggal_kalibrasi_berikut')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_sarana');
    }
};
