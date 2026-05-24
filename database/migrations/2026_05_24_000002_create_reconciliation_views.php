<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_reconciliation_summary');
        DB::statement('
            CREATE VIEW v_reconciliation_summary AS
            SELECT
                source_type,
                status,
                COUNT(*) as total,
                ROUND(AVG(similarity_score), 2) as avg_score,
                prodi_id
            FROM reconciliation_suggestions
            GROUP BY source_type, status, prodi_id
        ');

        DB::statement('DROP VIEW IF EXISTS v_reconciliation_pending');
        DB::statement("
            CREATE VIEW v_reconciliation_pending AS
            SELECT
                rs.*,
                COALESCE(md.nama_depan, '') || ' ' || COALESCE(md.nama_belakang, '') as nama_di_sistem,
                rs.source_data->>'nama' as nama_di_excel,
                mp.nama_prodi
            FROM reconciliation_suggestions rs
            LEFT JOIN m_dosen md ON md.id = rs.target_id
            LEFT JOIN m_prodi mp ON mp.id = rs.prodi_id
            WHERE rs.status = 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_reconciliation_summary');
        DB::statement('DROP VIEW IF EXISTS v_reconciliation_pending');
    }
};
