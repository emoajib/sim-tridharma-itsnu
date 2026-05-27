<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->string('nuptk', 30)->nullable()->unique()->after('nidn');
            $table->string('kepangkatan', 100)->nullable()->after('jabatan_fungsional');
            $table->string('rumpun_ilmu', 255)->nullable()->after('pendidikan_terakhir');
            $table->string('status_serdos', 100)->nullable()->after('status_aktivitas');
            $table->string('status_pegawai', 50)->nullable()->after('status_serdos');
            $table->string('ikatan_kerja', 100)->nullable()->after('status_pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('m_dosen', function (Blueprint $table) {
            $table->dropColumn(['nuptk', 'kepangkatan', 'rumpun_ilmu', 'status_serdos', 'status_pegawai', 'ikatan_kerja']);
        });
    }
};
