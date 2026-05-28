<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Pkm;
use App\Models\ImportHistory;
use App\Models\PeriodeAkademik;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LppmPkmImport implements ToModel, WithHeadingRow
{
    private int $rowNumber = 0;
    private int $successCount = 0;
    private array $failures = [];

    public function __construct(
        private readonly int $historyId,
    ) {}

    public function model(array $row)
    {
        $this->rowNumber++;

        $judul = $row['judul_pkm'] ?? null;
        $nidn = $row['ketua_pelaksana_nidn'] ?? $row['ketua_pelaksana_nidn'] ?? null;

        if (!$judul) {
            $this->failures[] = "Row {$this->rowNumber}: Judul PKM kosong";
            return null;
        }

        $dosen = null;
        if ($nidn) {
            $dosen = Dosen::where('nidn', $nidn)->first();
            if (!$dosen) {
                $this->failures[] = "Row {$this->rowNumber}: Dosen dengan NIDN {$nidn} tidak ditemukan";
            }
        }

        $periode = PeriodeAkademik::where('is_active', true)->first();

        $this->successCount++;

        return new Pkm([
            'dosen_id' => $dosen?->id,
            'prodi_id' => $dosen?->prodi_id,
            'periode_id' => $periode?->id,
            'judul_pkm' => $judul,
            'lokasi' => $row['lokasi_kegiatan'] ?? $row['lokasi'] ?? null,
            'sumber_dana' => $row['sumber_dana'] ?? 'Hibah Internal LPPM',
            'jumlah_dana' => is_numeric($row['jumlah_dana_rp'] ?? $row['jumlah_dana'] ?? 0)
                ? (float) ($row['jumlah_dana_rp'] ?? $row['jumlah_dana'] ?? 0) : 0,
            'tahun_pelaksanaan' => $row['tahun_pelaksanaan'] ?? date('Y'),
            'is_verified' => true,
        ]);
    }

    public function __destruct()
    {
        ImportHistory::where('id', $this->historyId)->update([
            'success_rows' => $this->successCount,
            'failed_rows' => count($this->failures),
            'total_rows' => $this->rowNumber,
            'errors' => $this->failures,
        ]);
    }
}
