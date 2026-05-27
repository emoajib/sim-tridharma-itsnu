<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE m_dosen ALTER COLUMN pendidikan_terakhir TYPE VARCHAR(150)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE m_dosen MODIFY pendidikan_terakhir VARCHAR(150) NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE m_dosen ALTER COLUMN pendidikan_terakhir TYPE VARCHAR(50)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE m_dosen MODIFY pendidikan_terakhir VARCHAR(50) NULL');
        }
    }
};
