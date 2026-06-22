<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fkFixes = [
        // table => [['constraint_name', 'column', 'references_table']]
        'm_dosen' => [
            ['fk_m_dosen_prodi_id', 'prodi_id', 'm_prodi'],
        ],
        'trx_publikasi' => [
            ['fk_trx_publikasi_dosen_id', 'dosen_id', 'm_dosen'],
            ['trx_publikasi_prodi_id_foreign', 'prodi_id', 'm_prodi'],
            ['trx_publikasi_periode_id_foreign', 'periode_id', 'm_periode_akademik'],
        ],
        'trx_penelitian' => [
            ['fk_trx_penelitian_dosen_id', 'dosen_id', 'm_dosen'],
            ['trx_penelitian_prodi_id_foreign', 'prodi_id', 'm_prodi'],
            ['trx_penelitian_periode_id_foreign', 'periode_id', 'm_periode_akademik'],
        ],
        'trx_pkm' => [
            ['fk_trx_pkm_dosen_id', 'dosen_id', 'm_dosen'],
            ['trx_pkm_prodi_id_foreign', 'prodi_id', 'm_prodi'],
            ['trx_pkm_periode_id_foreign', 'periode_id', 'm_periode_akademik'],
        ],
        'integrasi_sinta_publikasi' => [
            ['integrasi_sinta_publikasi_dosen_id_foreign', 'dosen_id', 'm_dosen'],
            ['integrasi_sinta_publikasi_publikasi_id_foreign', 'publikasi_id', 'trx_publikasi'],
        ],
        'integrasi_pddikti_dosen' => [
            ['integrasi_pddikti_dosen_dosen_id_foreign', 'dosen_id', 'm_dosen'],
        ],
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('SET statement_timeout = 60000;');

        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                $this->dropForeignKeySafe($table, $constraint);
            }
        }

        // Re-add FKs with nullOnDelete
        DB::statement('ALTER TABLE m_dosen ADD CONSTRAINT fk_m_dosen_prodi_id FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT fk_trx_publikasi_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT trx_publikasi_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT trx_publikasi_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT fk_trx_penelitian_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT trx_penelitian_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT trx_penelitian_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT fk_trx_pkm_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT trx_pkm_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT trx_pkm_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE integrasi_sinta_publikasi ADD CONSTRAINT integrasi_sinta_publikasi_dosen_id_foreign FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE SET NULL;');
        DB::statement('ALTER TABLE integrasi_sinta_publikasi ADD CONSTRAINT integrasi_sinta_publikasi_publikasi_id_foreign FOREIGN KEY (publikasi_id) REFERENCES trx_publikasi(id) ON DELETE SET NULL;');

        DB::statement('ALTER TABLE integrasi_pddikti_dosen ADD CONSTRAINT integrasi_pddikti_dosen_dosen_id_foreign FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE SET NULL;');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Drop all new FKs first
        foreach ($this->fkFixes as $table => $constraints) {
            foreach ($constraints as [$constraint, $column, $references]) {
                $this->dropForeignKeySafe($table, $constraint);
            }
        }

        // Restore original cascadeOnDelete
        DB::statement('ALTER TABLE m_dosen ADD CONSTRAINT fk_m_dosen_prodi_id FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE CASCADE;');

        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT fk_trx_publikasi_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT trx_publikasi_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_publikasi ADD CONSTRAINT trx_publikasi_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE CASCADE;');

        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT fk_trx_penelitian_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT trx_penelitian_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_penelitian ADD CONSTRAINT trx_penelitian_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE CASCADE;');

        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT fk_trx_pkm_dosen_id FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT trx_pkm_prodi_id_foreign FOREIGN KEY (prodi_id) REFERENCES m_prodi(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE trx_pkm ADD CONSTRAINT trx_pkm_periode_id_foreign FOREIGN KEY (periode_id) REFERENCES m_periode_akademik(id) ON DELETE CASCADE;');

        DB::statement('ALTER TABLE integrasi_sinta_publikasi ADD CONSTRAINT integrasi_sinta_publikasi_dosen_id_foreign FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE CASCADE;');
        DB::statement('ALTER TABLE integrasi_sinta_publikasi ADD CONSTRAINT integrasi_sinta_publikasi_publikasi_id_foreign FOREIGN KEY (publikasi_id) REFERENCES trx_publikasi(id) ON DELETE CASCADE;');

        DB::statement('ALTER TABLE integrasi_pddikti_dosen ADD CONSTRAINT integrasi_pddikti_dosen_dosen_id_foreign FOREIGN KEY (dosen_id) REFERENCES m_dosen(id) ON DELETE CASCADE;');
    }

    private function dropForeignKeySafe(string $table, string $constraint): void
    {
        $exists = DB::select(
            "SELECT 1 FROM pg_constraint WHERE conname = ? AND conrelid = ?::regclass",
            [$constraint, $table]
        );

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
        }
    }
};
