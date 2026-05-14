<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_generator_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('m_periode_akademik')->cascadeOnDelete();
            $table->string('jenis_dokumen', 50);
            $table->string('judul', 200);
            $table->string('file_path', 500)->nullable();
            $table->string('status', 30);
            $table->text('prompt_text')->nullable();
            $table->text('hasil_text')->nullable();
            $table->string('generated_by', 50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_generator_history');
    }
};
