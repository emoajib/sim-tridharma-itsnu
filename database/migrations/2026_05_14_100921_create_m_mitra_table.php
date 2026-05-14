<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_mitra', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mitra', 200);
            $table->string('jenis_mitra', 50);
            $table->text('alamat')->nullable();
            $table->string('kontak', 100)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_mitra');
    }
};
