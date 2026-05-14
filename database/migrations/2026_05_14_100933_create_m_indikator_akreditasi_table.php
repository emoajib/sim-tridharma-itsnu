<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_indikator_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_indikator', 30)->unique();
            $table->text('nama_indikator');
            $table->string('kriteria', 10);
            $table->decimal('bobot', 5)->default(0);
            $table->text('target')->nullable();
            $table->string('jenis_akreditasi', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_indikator_akreditasi');
    }
};
