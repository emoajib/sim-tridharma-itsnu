<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('dosen_id')->nullable()->constrained('m_dosen')->cascadeOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dosen_id');
            $table->dropConstrainedForeignId('prodi_id');
            $table->dropColumn('is_active');
        });
    }
};
