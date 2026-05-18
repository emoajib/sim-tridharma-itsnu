<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_indikator_akreditasi', function (Blueprint $table) {
            $table->foreignId('instrumen_id')->nullable()->constrained('m_instrumen_akreditasi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('m_indikator_akreditasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instrumen_id');
        });
    }
};
