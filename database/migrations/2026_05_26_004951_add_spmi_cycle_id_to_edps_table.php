<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_edps', function (Blueprint $table) {
            $table->unsignedBigInteger('spmi_cycle_id')->nullable()->after('periode_id');
            
            $table->index(['spmi_cycle_id', 'status']);
        });

        // Backfill: Match by prodi_id and periode_id
        DB::statement("
            UPDATE trx_edps edps
            SET spmi_cycle_id = sc.id
            FROM spmi_cycles sc
            WHERE edps.prodi_id = sc.prodi_id 
                AND edps.periode_id = sc.periode_id
                AND edps.spmi_cycle_id IS NULL
        ");

        Schema::table('trx_edps', function (Blueprint $table) {
            $table->foreign('spmi_cycle_id')
                ->references('id')
                ->on('spmi_cycles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trx_edps', function (Blueprint $table) {
            $table->dropForeign(['spmi_cycle_id']);
            $table->dropIndex(['spmi_cycle_id', 'status']);
            $table->dropColumn('spmi_cycle_id');
        });
    }
};
