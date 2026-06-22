<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_sync_sister_riwayat');

        DB::statement("
            CREATE VIEW v_sync_sister_riwayat AS
            SELECT 
                ipd.id,
                ipd.dosen_id,
                md.nama_depan || ' ' || COALESCE(md.nama_belakang, '') AS nama_dosen,
                md.nidn,
                'pendidikan' AS jenis_riwayat,
                ipd.data_dari_pddikti->>'gelar' AS gelar,
                ipd.data_dari_pddikti->>'institusi' AS institusi,
                ipd.data_dari_pddikti->>'tahun_lulus' AS tahun_lulus,
                ipd.status_sinkron,
                ipd.resolved_at,
                ipd.created_at,
                ipd.updated_at
            FROM integrasi_pddikti_dosen ipd
            JOIN m_dosen md ON md.id = ipd.dosen_id
            WHERE ipd.data_dari_pddikti->>'gelar' IS NOT NULL
            UNION ALL
            SELECT 
                tp.id + 1000000 AS id,
                tp.dosen_id,
                md.nama_depan || ' ' || COALESCE(md.nama_belakang, ''),
                md.nidn,
                'penelitian',
                tp.judul_penelitian,
                tp.sumber_dana,
                tp.tahun_pelaksanaan::text,
                'synced',
                NULL::timestamp,
                tp.created_at,
                tp.updated_at
            FROM trx_penelitian tp
            JOIN m_dosen md ON md.id = tp.dosen_id
            UNION ALL
            SELECT 
                tpub.id + 2000000 AS id,
                tpub.dosen_id,
                md.nama_depan || ' ' || COALESCE(md.nama_belakang, ''),
                md.nidn,
                'publikasi',
                tpub.judul_publikasi,
                tpub.jenis_publikasi,
                tpub.tahun,
                'synced',
                NULL::timestamp,
                tpub.created_at,
                tpub.updated_at
            FROM trx_publikasi tpub
            JOIN m_dosen md ON md.id = tpub.dosen_id
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS v_sync_sister_riwayat');

        // Restore original stub view
        DB::statement("
            CREATE VIEW v_sync_sister_riwayat AS
            SELECT id, dosen_id, 'pendidikan' as jenis_riwayat, status_sinkron, created_at, updated_at
            FROM integrasi_pddikti_dosen
            WHERE 1=0
        ");
    }
};
