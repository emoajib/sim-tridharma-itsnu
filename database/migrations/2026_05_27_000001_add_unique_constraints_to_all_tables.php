<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function constraints(): array
    {
        return [
            'trx_bkd' => [
                'columns' => ['dosen_id', 'periode_id'],
                'name' => 'uq_trx_bkd_dosen_periode',
            ],
            'trx_kegiatan_pendidikan' => [
                'columns' => ['dosen_id', 'periode_id', 'nama_kegiatan'],
                'name' => 'uq_trx_pendidikan_dosen_periode_kegiatan',
            ],
            'trx_penelitian' => [
                'columns' => ['dosen_id', 'judul_penelitian', 'tahun_pelaksanaan'],
                'name' => 'uq_trx_penelitian_dosen_judul_tahun',
            ],
            'trx_publikasi' => [
                'columns' => ['dosen_id', 'judul_publikasi', 'tahun'],
                'name' => 'uq_trx_publikasi_dosen_judul_tahun',
            ],
            'trx_pkm' => [
                'columns' => ['dosen_id', 'judul_pkm', 'tahun_pelaksanaan'],
                'name' => 'uq_trx_pkm_dosen_judul_tahun',
            ],
            'trx_penunjang' => [
                'columns' => ['dosen_id', 'nama_kegiatan', 'tahun'],
                'name' => 'uq_trx_penunjang_dosen_kegiatan_tahun',
            ],
            'trx_mahasiswa_bimbingan' => [
                'columns' => ['dosen_id', 'mahasiswa_id', 'periode_id', 'jenis_bimbingan'],
                'name' => 'uq_trx_bimbingan_dosen_mhs_periode',
            ],
            'trx_audit_mutu' => [
                'columns' => ['prodi_id', 'periode_id', 'judul_audit'],
                'name' => 'uq_trx_audit_prodi_periode_judul',
            ],
            'm_kurikulum' => [
                'columns' => ['prodi_id', 'nama_kurikulum'],
                'name' => 'uq_m_kurikulum_prodi_nama',
            ],
            'm_mitra' => [
                'columns' => ['nama_mitra'],
                'name' => 'uq_m_mitra_nama',
            ],
            'm_kerjasama' => [
                'columns' => ['mitra_id', 'nomor_mou'],
                'name' => 'uq_m_kerjasama_mitra_mou',
            ],
            'm_sarana' => [
                'columns' => ['prodi_id', 'nama_sarana'],
                'name' => 'uq_m_sarana_prodi_nama',
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->constraints() as $table => $config) {
            $columns = $config['columns'];
            $name = $config['name'];

            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $this->cleanDuplicates($table, $columns);
                Schema::table($table, function (Blueprint $table) use ($columns, $name) {
                    $table->unique($columns, $name);
                });
            } catch (\Exception $e) {
                // Log and continue
            }
        }
    }

    public function down(): void
    {
        foreach ($this->constraints() as $table => $config) {
            $name = $config['name'];
            if (!Schema::hasTable($table)) {
                continue;
            }
            try {
                Schema::table($table, function (Blueprint $table) use ($name) {
                    $table->dropIndex($name);
                });
            } catch (\Exception $e) {
                // Log and continue
            }
        }
    }

    private function cleanDuplicates(string $table, array $columns): void
    {
        $colList = implode(', ', $columns);

        // ── Find ALL duplicates across both active and soft-deleted rows ──
        // Standard UNIQUE constraints see ALL rows regardless of deleted_at,
        // so we must clean every duplicate to avoid constraint failure.
        $duplicates = DB::select("
            SELECT {$colList}, COUNT(*) as cnt
            FROM {$table}
            GROUP BY {$colList}
            HAVING COUNT(*) > 1
        ");

        if (count($duplicates) === 0) {
            return;
        }

        foreach ($duplicates as $dup) {
            $conditions = [];
            $params = [];
            foreach ($columns as $col) {
                $val = $dup->$col;
                if (is_null($val)) {
                    $conditions[] = "{$col} IS NULL";
                } else {
                    $conditions[] = "{$col} = ?";
                    $params[] = $val;
                }
            }
            $whereClause = implode(' AND ', $conditions);

            // Fetch ALL rows (including soft-deleted) ordered by recency
            $rows = DB::select("
                SELECT id, deleted_at FROM {$table}
                WHERE {$whereClause}
                ORDER BY COALESCE(created_at, '1970-01-01') DESC
            ", $params);

            // Keep only the most recent row, hard-delete the rest
            // Hard-delete is required because soft-deleting still leaves
            // duplicate values visible to the UNIQUE constraint.
            $keep = true;
            foreach ($rows as $row) {
                if ($keep) {
                    $keep = false;
                    continue;
                }
                DB::delete('DELETE FROM ' . $table . ' WHERE id = ?', [$row->id]);
            }
        }
    }
};
