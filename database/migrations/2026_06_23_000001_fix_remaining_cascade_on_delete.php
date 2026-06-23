<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fkFixes = [
        'users' => [
            ['users_dosen_id_foreign', 'dosen_id', 'm_dosen'],
            ['users_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'm_mahasiswa' => [
            ['m_mahasiswa_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'm_alumni' => [
            ['m_alumni_mahasiswa_id_foreign', 'mahasiswa_id', 'm_mahasiswa'],
            ['m_alumni_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'm_cpl' => [
            ['m_cpl_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'm_rps' => [
            ['m_rps_mata_kuliah_id_foreign', 'mata_kuliah_id', 'm_mata_kuliah'],
            ['m_rps_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'doc_bukti' => [
            ['doc_bukti_dosen_id_foreign', 'dosen_id', 'm_dosen'],
            ['doc_bukti_prodi_id_foreign', 'prodi_id', 'm_prodi'],
        ],
        'integrasi_pddikti_dosen' => [
            ['integrasi_pddikti_dosen_dosen_id_foreign', 'dosen_id', 'm_dosen'],
        ],
        'trx_risk_register' => [
            ['trx_risk_register_prodi_id_foreign', 'prodi_id', 'm_prodi'],
            ['trx_risk_register_periode_id_foreign', 'periode_id', 'm_periode_akademik'],
        ],
        'trx_edps' => [
            ['trx_edps_prodi_id_foreign', 'prodi_id', 'm_prodi'],
            ['trx_edps_periode_id_foreign', 'periode_id', 'm_periode_akademik'],
        ],
        'trx_sertifikat_ostamaru' => [
            ['trx_sertifikat_ostamaru_mahasiswa_id_foreign', 'mahasiswa_id', 'm_mahasiswa'],
        ],
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                $this->dropForeignKeySafe($table, $constraint);
            }
        }

        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$column}) REFERENCES {$references}(id) ON DELETE SET NULL");
            }
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                $this->dropForeignKeySafe($table, $constraint);
            }
        }

        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$column}) REFERENCES {$references}(id) ON DELETE CASCADE");
            }
        }
    }

    private function dropForeignKeySafe(string $table, string $constraint): void
    {
        try {
            $exists = DB::select(
                "SELECT 1 FROM pg_constraint WHERE conname = ? AND conrelid = ?::regclass",
                [$constraint, $table]
            );
            if ($exists) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
            }
        } catch (\Exception $e) {
            // Silently skip if constraint doesn't exist
        }
    }
};
