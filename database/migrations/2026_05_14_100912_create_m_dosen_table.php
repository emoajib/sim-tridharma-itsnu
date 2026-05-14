<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_dosen', function (Blueprint $table) {
            $table->id();
            $table->string('nidn', 20)->unique();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nama_depan', 100);
            $table->string('nama_belakang', 100)->nullable();
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('jabatan_fungsional', 100)->nullable();
            $table->string('status_aktivitas', 30)->default('aktif');
            $table->string('email', 100)->nullable()->unique();
            $table->string('telepon', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_dosen');
    }
};
