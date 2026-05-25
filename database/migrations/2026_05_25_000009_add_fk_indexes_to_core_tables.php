<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master data FK indexes
        Schema::table('m_prodi', function (Blueprint $table) {
            $table->index('fakultas_id');
        });
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_mahasiswa', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_mata_kuliah', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_kurikulum', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_cpl', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_kerjasama', function (Blueprint $table) {
            $table->index(['mitra_id', 'prodi_id']);
        });
        Schema::table('m_sarana', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_kuisioner_tracer', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('m_prodi_keahlian', function (Blueprint $table) {
            $table->index('prodi_id');
        });
        Schema::table('trx_mahasiswa_bimbingan', function (Blueprint $table) {
            $table->index(['dosen_id', 'mahasiswa_id']);
            $table->index('prodi_id');
            $table->index('periode_id');
        });

        // Status/date filter indexes
        Schema::table('m_prodi', function (Blueprint $table) {
            $table->index('jenjang');
        });
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->index('status_aktivitas');
        });
        Schema::table('trx_publikasi', function (Blueprint $table) {
            $table->index(['tahun', 'tingkat']);
        });
        Schema::table('trx_penelitian', function (Blueprint $table) {
            $table->index('tahun_pelaksanaan');
        });
    }

    public function down(): void
    {
        // Reverse FK indexes
        Schema::table('m_prodi', function (Blueprint $table) {
            $table->dropIndex(['fakultas_id']);
            $table->dropIndex(['jenjang']);
        });
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
            $table->dropIndex(['status_aktivitas']);
        });
        Schema::table('m_mahasiswa', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_mata_kuliah', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_kurikulum', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_cpl', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_kerjasama', function (Blueprint $table) {
            $table->dropIndex(['mitra_id', 'prodi_id']);
        });
        Schema::table('m_sarana', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_kuisioner_tracer', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('m_prodi_keahlian', function (Blueprint $table) {
            $table->dropIndex(['prodi_id']);
        });
        Schema::table('trx_mahasiswa_bimbingan', function (Blueprint $table) {
            $table->dropIndex(['dosen_id', 'mahasiswa_id']);
            $table->dropIndex(['prodi_id']);
            $table->dropIndex(['periode_id']);
        });
        Schema::table('trx_publikasi', function (Blueprint $table) {
            $table->dropIndex(['tahun', 'tingkat']);
        });
        Schema::table('trx_penelitian', function (Blueprint $table) {
            $table->dropIndex(['tahun_pelaksanaan']);
        });
    }
};
