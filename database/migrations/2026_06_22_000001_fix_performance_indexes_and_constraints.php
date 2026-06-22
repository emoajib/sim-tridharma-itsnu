<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement('SET statement_timeout = 30000;');

        // --- 1. Fix wrong table names in performance indexes ---
        // Original migration tried to create indexes on non-existent tables
        // (m_knowledge_base_documents, m_knowledge_base_chunks, trx_security_audit_logs)
        // These silently failed — create them on the correct tables now.

        $fixIndexes = [
            'idx_knowledge_base_documents_category' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_knowledge_base_documents_category ON knowledge_base_documents (category_id)',
            'idx_knowledge_base_chunks_document' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_knowledge_base_chunks_document ON knowledge_base_chunks (document_id)',
            'idx_security_audit_logs_created' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_security_audit_logs_created ON security_audit_logs (created_at)',
            'idx_security_audit_logs_user' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_security_audit_logs_user ON security_audit_logs (user_id)',
        ];

        foreach ($fixIndexes as $name => $sql) {
            try {
                DB::statement($sql);
                Log::info("Index created: {$name}");
            } catch (\Throwable $e) {
                Log::warning("Index creation skipped for {$name}: {$e->getMessage()}");
            }
        }

        // --- 2. Unique constraints for SINTA sync tables ---
        // Prevents duplicate records from repeated syncs (see AGENTS.md: upsert requirement)

        $sintaTables = [
            'integrasi_sinta_publikasi' => ['dosen_id', 'judul'],
            'integrasi_sinta_penelitian' => ['dosen_id', 'judul'],
            'integrasi_sinta_pkm' => ['dosen_id', 'judul'],
        ];

        foreach ($sintaTables as $table => $columns) {
            $constraintName = "uq_{$table}_dosen_judul";
            $colList = implode(', ', $columns);

            try {
                $existing = DB::select(
                    "SELECT 1 FROM information_schema.table_constraints WHERE constraint_name = ? AND table_name = ?",
                    [$constraintName, $table]
                );

                if (!empty($existing)) {
                    Log::info("Constraint already exists: {$constraintName}");
                    continue;
                }

                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} UNIQUE ({$colList})");
                Log::info("Unique constraint added: {$constraintName} on {$table}");
            } catch (\Throwable $e) {
                Log::warning("Unique constraint skipped for {$table}: {$e->getMessage()}");
            }
        }

        // --- 3. Add spmi_cycle_id to trx_rtm_action_items and m_spmi_dokumen ---
        // Fixes phantom HasMany relations in SpmiCycle model

        if (!Schema::hasColumn('trx_rtm_action_items', 'spmi_cycle_id')) {
            Schema::table('trx_rtm_action_items', function ($table) {
                $table->foreignId('spmi_cycle_id')->nullable()->constrained('spmi_cycles')->nullOnDelete();
            });
            Log::info('Added spmi_cycle_id to trx_rtm_action_items');
        }

        if (!Schema::hasColumn('m_spmi_dokumen', 'spmi_cycle_id')) {
            Schema::table('m_spmi_dokumen', function ($table) {
                $table->foreignId('spmi_cycle_id')->nullable()->constrained('spmi_cycles')->nullOnDelete();
            });
            Log::info('Added spmi_cycle_id to m_spmi_dokumen');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        $indexes = [
            'idx_knowledge_base_documents_category',
            'idx_knowledge_base_chunks_document',
            'idx_security_audit_logs_created',
            'idx_security_audit_logs_user',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }

        $sintaTables = ['integrasi_sinta_publikasi', 'integrasi_sinta_penelitian', 'integrasi_sinta_pkm'];
        foreach ($sintaTables as $table) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS uq_{$table}_dosen_judul");
        }

        if (Schema::hasColumn('trx_rtm_action_items', 'spmi_cycle_id')) {
            Schema::table('trx_rtm_action_items', function ($table) {
                $table->dropForeign(['spmi_cycle_id']);
                $table->dropColumn('spmi_cycle_id');
            });
        }

        if (Schema::hasColumn('m_spmi_dokumen', 'spmi_cycle_id')) {
            Schema::table('m_spmi_dokumen', function ($table) {
                $table->dropForeign(['spmi_cycle_id']);
                $table->dropColumn('spmi_cycle_id');
            });
        }
    }
};
