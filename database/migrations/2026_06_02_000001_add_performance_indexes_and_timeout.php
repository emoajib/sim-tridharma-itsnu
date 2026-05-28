<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Performance indexes for common query patterns
        $hasCorrectiveDeadline = DB::select("SELECT 1 FROM information_schema.columns WHERE table_name = 'trx_capa' AND column_name = 'corrective_deadline'");
        $hasProdiId = DB::select("SELECT 1 FROM information_schema.columns WHERE table_name = 'trx_capa' AND column_name = 'prodi_id'");

        if ($hasCorrectiveDeadline) {
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_capa_status_deadline ON trx_capa (status, corrective_deadline)');
        }
        if ($hasProdiId) {
            DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_capa_prodi_id ON trx_capa (prodi_id)');
        }

        $indexes = [
            'idx_trx_audit_mutu_prodi_periode' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_audit_mutu_prodi_periode ON trx_audit_mutu (prodi_id, periode_id)',
            'idx_trx_audit_mutu_status' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_audit_mutu_status ON trx_audit_mutu (status)',
            'idx_trx_edps_prodi_periode' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_edps_prodi_periode ON trx_edps (prodi_id, periode_id)',
            'idx_trx_rtm_prodi_periode' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_rtm_prodi_periode ON trx_rtm (prodi_id, periode_id)',
            'idx_trx_risk_register_prodi' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_risk_register_prodi ON trx_risk_register (prodi_id)',
            'idx_trx_survey_spmi_prodi' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_survey_spmi_prodi ON trx_survey_spmi (prodi_id)',
            'idx_trx_rekonsiliasi_status' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_rekonsiliasi_status ON reconciliation_suggestions (status, similarity_score)',
            'idx_trx_security_audit_logs_created' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_security_audit_logs_created ON trx_security_audit_logs (created_at)',
            'idx_trx_security_audit_logs_user' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_security_audit_logs_user ON trx_security_audit_logs (user_id)',
            'idx_m_dosen_nama' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_dosen_nama ON m_dosen (nama_depan, nama_belakang)',
            'idx_m_dosen_nidn' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_dosen_nidn ON m_dosen (nidn) WHERE nidn IS NOT NULL',
            'idx_m_prodi_fakultas_jenjang' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_prodi_fakultas_jenjang ON m_prodi (fakultas_id, jenjang)',
            'idx_users_email' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_email ON users (email)',
            'idx_m_knowledge_base_documents_category' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_knowledge_base_documents_category ON m_knowledge_base_documents (category_id)',
            'idx_m_knowledge_base_chunks_document' => 'CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_knowledge_base_chunks_document ON m_knowledge_base_chunks (document_id)',
        ];

        foreach ($indexes as $name => $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                Log::warning("Index creation skipped for {$name}: {$e->getMessage()}");
            }
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        $indexes = [
            'idx_trx_capa_status_deadline',
            'idx_trx_capa_prodi_id',
            'idx_trx_audit_mutu_prodi_periode',
            'idx_trx_audit_mutu_status',
            'idx_trx_edps_prodi_periode',
            'idx_trx_rtm_prodi_periode',
            'idx_trx_risk_register_prodi',
            'idx_trx_survey_spmi_prodi',
            'idx_trx_rekonsiliasi_status',
            'idx_trx_security_audit_logs_created',
            'idx_trx_security_audit_logs_user',
            'idx_m_dosen_nama',
            'idx_m_dosen_nidn',
            'idx_m_prodi_fakultas_jenjang',
            'idx_users_email',
            'idx_m_knowledge_base_documents_category',
            'idx_m_knowledge_base_chunks_document',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }
    }
};
