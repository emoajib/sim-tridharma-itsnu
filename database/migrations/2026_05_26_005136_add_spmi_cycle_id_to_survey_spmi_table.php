<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_survey_spmi', function (Blueprint $table) {
            $table->unsignedBigInteger('spmi_cycle_id')->nullable()->after('periode_id');
            
            $table->index(['spmi_cycle_id', 'responden_type']);
        });

        // Backfill: Match by periode_id
        // Only on PostgreSQL — SQLite doesn't support UPDATE...FROM
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE trx_survey_spmi s
                SET spmi_cycle_id = sc.id
                FROM spmi_cycles sc
                WHERE s.periode_id = sc.periode_id
                    AND s.spmi_cycle_id IS NULL
            ");
        }

        Schema::table('trx_survey_spmi', function (Blueprint $table) {
            $table->foreign('spmi_cycle_id')
                ->references('id')
                ->on('spmi_cycles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trx_survey_spmi', function (Blueprint $table) {
            $table->dropForeign(['spmi_cycle_id']);
            $table->dropIndex(['spmi_cycle_id', 'responden_type']);
            $table->dropColumn('spmi_cycle_id');
        });
    }
};
