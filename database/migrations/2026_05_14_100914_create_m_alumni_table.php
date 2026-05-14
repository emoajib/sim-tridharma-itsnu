<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->nullable()->unique()->constrained('m_mahasiswa')->cascadeOnDelete();
            $table->string('nim', 30);
            $table->string('nama', 200);
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->string('tahun_lulus', 10);
            $table->integer('masa_tunggu')->nullable();
            $table->decimal('gaji_pertama', 15)->nullable();
            $table->string('pekerjaan', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_alumni');
    }
};
