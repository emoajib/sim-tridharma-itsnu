<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 30)->unique();
            $table->string('nama', 200);
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->string('angkatan', 10);
            $table->string('status', 30)->default('aktif');
            $table->string('email', 100)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_mahasiswa');
    }
};
