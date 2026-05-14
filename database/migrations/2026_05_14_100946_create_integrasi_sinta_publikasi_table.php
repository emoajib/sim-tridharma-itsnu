<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrasi_sinta_publikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('publikasi_id')->nullable()->constrained('trx_publikasi')->cascadeOnDelete();
            $table->text('judul');
            $table->json('data_dari_sinta');
            $table->string('status_sinkron', 30);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrasi_sinta_publikasi');
    }
};
