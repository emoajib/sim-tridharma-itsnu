<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DROP VIEW IF EXISTS v_sync_pddikti_dosen;
            CREATE VIEW v_sync_pddikti_dosen AS
            SELECT 
                ipd.id,
                ipd.dosen_id,
                ipd.nidn,
                md.nama_depan,
                md.nama_belakang,
                md.nidn as dosen_nidn,
                ipd.status_sinkron,
                ipd.data_dari_pddikti,
                ipd.data_di_sistem,
                ipd.created_at,
                ipd.updated_at
            FROM integrasi_pddikti_dosen ipd
            LEFT JOIN m_dosen md ON md.id = ipd.dosen_id
        ');

        DB::statement('
            DROP VIEW IF EXISTS v_sync_sinta_publikasi;
            CREATE VIEW v_sync_sinta_publikasi AS
            SELECT 
                isp.id,
                isp.dosen_id,
                isp.publikasi_id,
                isp.judul,
                isp.status_sinkron,
                isp.data_dari_sinta,
                isp.created_at,
                isp.updated_at
            FROM integrasi_sinta_publikasi isp
        ');

        DB::statement("
            DROP VIEW IF EXISTS v_sync_sister_riwayat;
            CREATE VIEW v_sync_sister_riwayat AS
            SELECT 
                id,
                dosen_id,
                'pendidikan' as jenis_riwayat,
                status_sinkron,
                created_at,
                updated_at
            FROM integrasi_pddikti_dosen
            WHERE 1=0
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_sync_pddikti_dosen');
        DB::statement('DROP VIEW IF EXISTS v_sync_sinta_publikasi');
        DB::statement('DROP VIEW IF EXISTS v_sync_sister_riwayat');
    }
};
