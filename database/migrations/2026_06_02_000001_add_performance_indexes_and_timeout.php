<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public bool $withinTransaction = false;

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        DB::statement('SET statement_timeout = 30000;');

        // Performance indexes for common query patterns
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_capa_status_deadline ON trx_capa (status, deadline)');
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_capa_prodi_id ON trx_capa (prodi_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_audit_mutu_prodi_periode ON trx_audit_mutu (prodi_id, periode_id)');
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_audit_mutu_status ON trx_audit_mutu (status)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_edps_prodi_periode ON trx_edps (prodi_id, periode_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_rtm_prodi_periode ON trx_rtm (prodi_id, periode_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_risk_register_prodi ON trx_risk_register (prodi_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_survey_spmi_prodi ON trx_survey_spmi (prodi_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_rekonsiliasi_status ON trx_reconciliation_suggestions (status, similarity_score)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_security_audit_logs_created ON trx_security_audit_logs (created_at)');
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_trx_security_audit_logs_user ON trx_security_audit_logs (user_id)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_dosen_nama ON m_dosen (nama_depan, nama_belakang)');
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_dosen_nidn ON m_dosen (nidn) WHERE nidn IS NOT NULL');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_prodi_fakultas_jenjang ON m_prodi (fakultas_id, jenjang)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_email ON users (email)');

        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_knowledge_base_documents_category ON m_knowledge_base_documents (category_id)');
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_m_knowledge_base_chunks_document ON m_knowledge_base_chunks (document_id)');
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
