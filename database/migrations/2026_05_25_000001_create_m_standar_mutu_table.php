<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_standar_mutu', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50);
            $table->string('kode_standar', 30)->unique();
            $table->string('nama_standar', 200);
            $table->text('deskripsi')->nullable();
            $table->string('sumber', 100)->nullable();
            $table->string('referensi_regulasi', 100)->nullable();
            $table->decimal('target_nilai', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('kategori');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_standar_mutu');
    }
};
