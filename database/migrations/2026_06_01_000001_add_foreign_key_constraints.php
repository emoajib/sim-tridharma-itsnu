<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * List of all FK constraints to add.
     *
     * Format: [table, column, referenced_table, on_delete_action, cleanup_strategy]
     * cleanup_strategy:
     *   'delete'   → remove orphan child rows (safe for transactional data)
     *   'set_null' → set FK column to NULL for orphan rows (safe for master data)
     */
    private array $constraints = [
        // ─── Transactional: cascade delete when parent is removed ───
        ['trx_bkd', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_kegiatan_pendidikan', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_penelitian', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_publikasi', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_pkm', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_penunjang', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_mahasiswa_bimbingan', 'dosen_id', 'm_dosen', 'CASCADE', 'delete'],
        ['trx_mahasiswa_bimbingan', 'mahasiswa_id', 'm_mahasiswa', 'CASCADE', 'delete'],
        ['trx_pemenuhan_indikator', 'prodi_id', 'm_prodi', 'CASCADE', 'delete'],
        ['trx_pemenuhan_indikator', 'indikator_id', 'm_indikator_akreditasi', 'CASCADE', 'delete'],
        ['trx_skor_akreditasi', 'prodi_id', 'm_prodi', 'CASCADE', 'delete'],
        ['trx_audit_mutu', 'prodi_id', 'm_prodi', 'CASCADE', 'delete'],
        ['trx_risk_register', 'prodi_id', 'm_prodi', 'CASCADE', 'delete'],

        // ─── Master data: restrict delete if children exist ───
        // Cleanup: set FK to NULL so constraint can be added; admin must fix data manually
        ['m_dosen', 'prodi_id', 'm_prodi', 'RESTRICT', 'set_null'],
        ['m_mahasiswa', 'prodi_id', 'm_prodi', 'RESTRICT', 'set_null'],
        ['m_mata_kuliah', 'prodi_id', 'm_prodi', 'RESTRICT', 'set_null'],
        ['m_kurikulum', 'prodi_id', 'm_prodi', 'RESTRICT', 'set_null'],
        ['m_sarana', 'prodi_id', 'm_prodi', 'RESTRICT', 'set_null'],
        ['m_prodi', 'fakultas_id', 'm_fakultas', 'RESTRICT', 'set_null'],

        // ─── Kerjasama: cascade (mitra data is reference for partnership docs) ───
        ['m_kerjasama', 'mitra_id', 'm_mitra', 'CASCADE', 'set_null'],

        // ─── Sessions: cascade delete when user is removed ───
        ['sessions', 'user_id', 'users', 'CASCADE', 'delete'],
    ];

    public function up(): void
    {
        // PostgreSQL supports ALTER TABLE ADD/DROP CONSTRAINT; SQLite does not support this syntax
        if (DB::getDriverName() !== 'pgsql') {
            $this->info('  → Skipping FK constraints (not supported on ' . DB::getDriverName() . ')');
            return;
        }

        foreach ($this->constraints as [$table, $column, $references, $onDelete, $cleanup]) {
            $this->cleanupOrphans($table, $column, $references, $cleanup);

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

        foreach ($this->constraints as [$table, $column, $references, $onDelete, $cleanup]) {
            $fkName = "fk_{$table}_{$column}";
            try {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$fkName}");
            } catch (\Exception $e) {
                // Ignore errors on rollback
            }
        }
    }

    /**
     * Clean orphan records before adding a foreign key constraint.
     */
    private function cleanupOrphans(string $table, string $column, string $references, string $strategy): void
    {
        try {
            // Check if table and column exist
            $check = DB::select("
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = ? AND column_name = ?
            ", [$table, $column]);

            if (empty($check)) {
                $this->warn("  ~ Skipped cleanup {$table}.{$column}: column does not exist");
                return;
            }

            $orphanCount = DB::selectOne("
                SELECT COUNT(*) AS cnt 
                FROM {$table} 
                WHERE {$column} IS NOT NULL 
                  AND {$column} NOT IN (SELECT id FROM {$references})
            ")->cnt ?? 0;

            if ((int) $orphanCount === 0) {
                return; // No orphans, nothing to clean
            }

            $this->info("  ~ Found {$orphanCount} orphan(s) in {$table}.{$column} → {$references}");

            if ($strategy === 'delete') {
                DB::statement("
                    DELETE FROM {$table} 
                    WHERE {$column} IS NOT NULL 
                      AND {$column} NOT IN (SELECT id FROM {$references})
                ");
                $this->info("  ~ Deleted {$orphanCount} orphan(s) from {$table}");
            } elseif ($strategy === 'set_null') {
                DB::statement("
                    UPDATE {$table} 
                    SET {$column} = NULL 
                    WHERE {$column} IS NOT NULL 
                      AND {$column} NOT IN (SELECT id FROM {$references})
                ");
                $this->info("  ~ Set {$column} = NULL for {$orphanCount} orphan(s) in {$table}");
            }
        } catch (\Exception $e) {
            $this->warn("  ! Cleanup failed for {$table}.{$column}: {$e->getMessage()}");
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
