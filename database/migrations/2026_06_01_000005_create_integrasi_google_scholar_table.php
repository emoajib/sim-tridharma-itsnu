<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrasi_google_scholar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->onDelete('set null');
            $table->string('google_scholar_id')->nullable();
            $table->string('judul');
            $table->text('penulis')->nullable();
            $table->string('jurnal')->nullable();
            $table->year('tahun')->nullable();
            $table->string('doi')->nullable();
            $table->text('url')->nullable();
            $table->integer('sitasi')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('sinkron_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrasi_google_scholar');
    }
};
