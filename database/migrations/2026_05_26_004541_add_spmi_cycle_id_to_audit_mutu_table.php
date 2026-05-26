<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_audit_mutu', function (Blueprint $table) {
            $table->unsignedBigInteger('spmi_cycle_id')->nullable()->after('standar_mutu_id');
            
            $table->index(['spmi_cycle_id', 'status']);
            $table->index(['prodi_id', 'spmi_cycle_id', 'status']);
        });

        // Backfill: Associate existing AuditMutu with appropriate SpmiCycle
        // Based on matching prodi_id and periode_id
        DB::statement("
            UPDATE trx_audit_mutu am
            SET spmi_cycle_id = sc.id
            FROM spmi_cycles sc
            WHERE am.prodi_id = sc.prodi_id 
                AND am.periode_id = sc.periode_id
                AND am.spmi_cycle_id IS NULL
        ");

        // Add foreign key constraint after backfill
        Schema::table('trx_audit_mutu', function (Blueprint $table) {
            $table->foreign('spmi_cycle_id')
                ->references('id')
                ->on('spmi_cycles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trx_audit_mutu', function (Blueprint $table) {
            $table->dropForeign(['spmi_cycle_id']);
            $table->dropIndex(['spmi_cycle_id', 'status']);
            $table->dropIndex(['prodi_id', 'spmi_cycle_id', 'status']);
            $table->dropColumn('spmi_cycle_id');
        });
    }
};
