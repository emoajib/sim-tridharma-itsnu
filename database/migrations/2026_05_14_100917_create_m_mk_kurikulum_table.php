<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_mk_kurikulum', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kurikulum_id')->constrained('m_kurikulum')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('m_mata_kuliah')->cascadeOnDelete();
            $table->integer('semester_rekomendasi')->nullable();
            $table->unique(['kurikulum_id', 'mata_kuliah_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_mk_kurikulum');
    }
};
