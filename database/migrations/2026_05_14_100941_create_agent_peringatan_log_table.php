<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_peringatan_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->cascadeOnDelete();
            $table->string('jenis_peringatan', 50);
            $table->string('tingkat', 20);
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_peringatan_log');
    }
};
