<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * List of all FK constraints to add.
     *
     * Format: [table, column, referenced_table, on_delete_action]
     *
     * Design rules:
     * - Transactional tables (trx_*) → CASCADE (delete child data when parent is deleted)
     * - Master data tables (m_*) → RESTRICT (prevent parent deletion if children exist)
     * - Special cases documented inline
     */
    private array $constraints = [
        // ─── Transactional: cascade delete when parent is removed ───
        ['trx_bkd', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_kegiatan_pendidikan', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_penelitian', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_publikasi', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_pkm', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_penunjang', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_mahasiswa_bimbingan', 'dosen_id', 'm_dosen', 'CASCADE'],
        ['trx_mahasiswa_bimbingan', 'mahasiswa_id', 'm_mahasiswa', 'CASCADE'],
        ['trx_pemenuhan_indikator', 'prodi_id', 'm_prodi', 'CASCADE'],
        ['trx_pemenuhan_indikator', 'indikator_id', 'm_indikator_akreditasi', 'CASCADE'],
        ['trx_skor_akreditasi', 'prodi_id', 'm_prodi', 'CASCADE'],
        ['trx_audit_mutu', 'prodi_id', 'm_prodi', 'CASCADE'],
        ['trx_risk_register', 'prodi_id', 'm_prodi', 'CASCADE'],

        // ─── Master data: restrict delete if children exist ───
        ['m_dosen', 'prodi_id', 'm_prodi', 'RESTRICT'],
        ['m_mahasiswa', 'prodi_id', 'm_prodi', 'RESTRICT'],
        ['m_mata_kuliah', 'prodi_id', 'm_prodi', 'RESTRICT'],
        ['m_kurikulum', 'prodi_id', 'm_prodi', 'RESTRICT'],
        ['m_sarana', 'prodi_id', 'm_prodi', 'RESTRICT'],
        ['m_prodi', 'fakultas_id', 'm_fakultas', 'RESTRICT'],

        // ─── Kerjasama: cascade (mitra data is reference for partnership docs) ───
        ['m_kerjasama', 'mitra_id', 'm_mitra', 'CASCADE'],

        // ─── Sessions: cascade delete when user is removed ───
        ['sessions', 'user_id', 'users', 'CASCADE'],
    ];

    public function up(): void
    {
        // PostgreSQL supports ALTER TABLE ADD/DROP CONSTRAINT; SQLite does not support this syntax
        if (DB::getDriverName() !== 'pgsql') {
            $this->info('  → Skipping FK constraints (not supported on ' . DB::getDriverName() . ')');
            return;
        }

        foreach ($this->constraints as [$table, $column, $references, $onDelete]) {
            $fkName = "fk_{$table}_{$column}";

            try {
                // Drop existing constraint with this name (safe to re-run)
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$fkName}");

                // Also drop Laravel-style constraint name if it exists
                $laravelFkName = "{$table}_{$column}_foreign";
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$laravelFkName}");

                // Add the new constraint
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$fkName} 
                    FOREIGN KEY ({$column}) REFERENCES {$references}(id) ON DELETE {$onDelete}");

                $this->info("  ✓ Added FK: {$table}.{$column} → {$references}(id) [{$onDelete}]");
            } catch (\Exception $e) {
                // Some tables may not have the column yet, or other transient issues
                $this->warn("  ! Skipped {$table}.{$column}: {$e->getMessage()}");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->constraints as [$table, $column, $references, $onDelete]) {
            $fkName = "fk_{$table}_{$column}";
            try {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$fkName}");
            } catch (\Exception $e) {
                // Ignore errors on rollback
            }
        }
    }

    private function info(string $msg): void
    {
        if (app()->runningInConsole()) {
            echo $msg . PHP_EOL;
        }
    }

    private function warn(string $msg): void
    {
        if (app()->runningInConsole()) {
            echo $msg . PHP_EOL;
        }
    }
};
