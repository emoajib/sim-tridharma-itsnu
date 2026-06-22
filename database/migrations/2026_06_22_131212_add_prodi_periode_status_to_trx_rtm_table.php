<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_rtm', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->constrained('m_prodi')->nullOnDelete();
            $table->foreignId('periode_id')->nullable()->constrained('m_periode_akademik')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trx_rtm', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropForeign(['periode_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['prodi_id', 'periode_id', 'status', 'created_by']);
        });
    }
};
