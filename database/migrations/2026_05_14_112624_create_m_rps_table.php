<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_rps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('m_mata_kuliah')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('m_periode_akademik')->nullOnDelete();
            $table->string('kode_rps', 50)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_rps');
    }
};
