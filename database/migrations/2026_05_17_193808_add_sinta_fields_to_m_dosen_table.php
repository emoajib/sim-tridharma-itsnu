<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->string('sinta_id', 50)->nullable()->after('nidn');
            $table->decimal('sinta_score_overall', 10, 2)->nullable()->after('jabatan_fungsional');
            $table->decimal('sinta_score_3yr', 10, 2)->nullable()->after('sinta_score_overall');
            $table->string('status_verifikasi_sinta', 50)->nullable()->after('sinta_score_3yr');
        });
    }

    public function down(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->dropColumn([
                'sinta_id',
                'sinta_score_overall',
                'sinta_score_3yr',
                'status_verifikasi_sinta',
            ]);
        });
    }
};
