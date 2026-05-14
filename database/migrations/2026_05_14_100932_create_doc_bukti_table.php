<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->string('nama_dokumen', 200);
            $table->string('file_path', 500);
            $table->string('file_type', 50);
            $table->integer('file_size')->nullable();
            $table->string('hash', 64)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('documentable_type', 100)->nullable();
            $table->bigInteger('documentable_id')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->index(['documentable_type', 'documentable_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_bukti');
    }
};
