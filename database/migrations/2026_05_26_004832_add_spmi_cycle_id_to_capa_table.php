<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_capa', function (Blueprint $table) {
            $table->unsignedBigInteger('spmi_cycle_id')->nullable()->after('audit_mutu_id');
            
            $table->index(['spmi_cycle_id', 'status']);
        });

        // Backfill via AuditMutu relationship
        DB::statement("
            UPDATE trx_capa c
            SET spmi_cycle_id = am.spmi_cycle_id
            FROM trx_audit_mutu am
            WHERE c.audit_mutu_id = am.id
                AND am.spmi_cycle_id IS NOT NULL
                AND c.spmi_cycle_id IS NULL
        ");

        Schema::table('trx_capa', function (Blueprint $table) {
            $table->foreign('spmi_cycle_id')
                ->references('id')
                ->on('spmi_cycles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trx_capa', function (Blueprint $table) {
            $table->dropForeign(['spmi_cycle_id']);
            $table->dropIndex(['spmi_cycle_id', 'status']);
            $table->dropColumn('spmi_cycle_id');
        });
    }
};
