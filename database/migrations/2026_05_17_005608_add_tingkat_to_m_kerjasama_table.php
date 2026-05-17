<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_kerjasama', function (Blueprint $table) {
            $table->string('tingkat', 50)->default('Lokal')->after('judul_kerjasama'); // Internasional, Nasional, Lokal
        });
    }

    public function down(): void
    {
        Schema::table('m_kerjasama', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });
    }
};
