<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Prodi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;

class SintaDosenImport implements ToModel, WithStartRow, SkipsEmptyRows
{
    public function startRow(): int
    {
        return 5; // Header row starts at row 5 in 8444.xls
    }

    public function model(array $row)
    {
        // row[1] = SINTA ID, row[2] = NIDN, row[3] = NAMA, row[5] = PRODI
        // row[6] = PENDIDIKAN TERAKHIR, row[7] = JABATAN FUNGSIONAL
        // row[12] = SINTA SCORE OVERALL (V3), row[13] = SINTA SCORE 3Yr (V3)
        // row[38] = STATUS VERIFIKASI
        
        $sintaId = $row[1] ?? null;
        $nidn = $row[2] ?? null;
        $name = $row[3] ?? null;
        $prodiName = $row[5] ?? null;
        $pendidikan = $row[6] ?? null;
        $jabatan = $row[7] ?? null;
        $scoreOverall = $row[12] ?? 0;
        $score3yr = $row[13] ?? 0;
        $statusVerif = $row[38] ?? null;

        if (!$nidn || !is_numeric($nidn)) return null;

        $prodi = Prodi::where('nama_prodi', 'like', "%{$prodiName}%")->first();
        $prodiId = $prodi ? $prodi->id : (Prodi::first()->id ?? 1);

        Log::info("SINTA Sync: Updating Dosen {$name} (NIDN: {$nidn}) with SINTA Score & Profile");

        return Dosen::updateOrCreate(
            ['nidn' => $nidn],
            [
                'sinta_id' => $sintaId,
                'nama_depan' => $name,
                'prodi_id' => $prodiId,
                'pendidikan_terakhir' => $pendidikan,
                'jabatan_fungsional' => $jabatan,
                'sinta_score_overall' => (float) $scoreOverall,
                'sinta_score_3yr' => (float) $score3yr,
                'status_verifikasi_sinta' => $statusVerif,
                'is_active' => true,
            ]
        );
    }
}
