<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_cpl_mk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('m_cpl')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('m_mata_kuliah')->cascadeOnDelete();
            $table->string('tingkat', 20)->nullable();
            $table->unique(['cpl_id', 'mata_kuliah_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_cpl_mk');
    }
};
