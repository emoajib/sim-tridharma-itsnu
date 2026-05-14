<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_tracer_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('m_alumni')->cascadeOnDelete();
            $table->foreignId('kuisioner_id')->constrained('m_kuisioner_tracer')->cascadeOnDelete();
            $table->json('jawaban');
            $table->timestamp('diisi_pada');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_tracer_jawaban');
    }
};
