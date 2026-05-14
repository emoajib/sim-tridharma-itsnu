<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_cpl', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cpl', 20);
            $table->foreignId('prodi_id')->constrained('m_prodi')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->string('jenis', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_cpl');
    }
};
