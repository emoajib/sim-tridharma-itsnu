<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\ReconciliationSuggestion;
use App\Services\Reconciliation\FuzzyMatchService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SintaDosenImport implements SkipsEmptyRows, ToModel, WithStartRow
{
    protected FuzzyMatchService $fuzzyMatch;

    public function __construct()
    {
        $this->fuzzyMatch = app(FuzzyMatchService::class);
    }

    public function startRow(): int
    {
        return 5;
    }

    public function model(array $row)
    {
        $sintaId = $row[1] ?? null;
        $nidn = $row[2] ?? null;
        $name = $row[3] ?? null;
        $prodiName = $row[5] ?? null;
        $pendidikan = $row[6] ?? null;
        $jabatan = $row[7] ?? null;
        $scoreOverall = $row[12] ?? 0;
        $score3yr = $row[13] ?? 0;
        $statusVerif = $row[38] ?? null;

        if (! $name) {
            return null;
        }

        $prodi = Prodi::where('nama_prodi', 'like', "%{$prodiName}%")->first();
        $prodiId = $prodi ? $prodi->id : (Prodi::first()->id ?? 1);

        if ($nidn && is_numeric($nidn)) {
            $existing = Dosen::where('nidn', $nidn)->first();
            if ($existing) {
                Log::info("SINTA Sync: Updating Dosen {$name} (NIDN: {$nidn})");

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

        $match = $this->fuzzyMatch->findBestMatch($name, $nidn, $prodiId);

        if ($match['score'] >= 95 && $match['target_id']) {
            $dosen = Dosen::find($match['target_id']);
            if ($dosen) {
                Log::info("SINTA Sync: Fuzzy auto-approved {$name} -> {$dosen->nama_depan} (score: {$match['score']})");
                $dosen->update([
                    'sinta_id' => $sintaId,
                    'sinta_score_overall' => (float) $scoreOverall,
                    'sinta_score_3yr' => (float) $score3yr,
                    'status_verifikasi_sinta' => $statusVerif,
                ]);
                return $dosen;
            }
        }

        ReconciliationSuggestion::create([
            'source_type' => 'sinta_dosen',
            'source_data' => [
                'sinta_id' => $sintaId,
                'nidn' => $nidn,
                'nama' => $name,
                'prodi' => $prodiName,
                'pendidikan_terakhir' => $pendidikan,
                'jabatan_fungsional' => $jabatan,
                'sinta_score_overall' => $scoreOverall,
                'sinta_score_3yr' => $score3yr,
            ],
            'target_table' => $match['target_id'] ? 'm_dosen' : null,
            'target_id' => $match['target_id'],
            'match_field' => $match['target_id'] ? 'nama' : null,
            'match_value' => $name,
            'similarity_score' => $match['score'],
            'confidence' => $match['confidence'],
            'status' => 'pending',
            'prodi_id' => $prodiId,
            'suggested_by' => 'system',
        ]);

        Log::info("SINTA Sync: Created reconciliation suggestion for {$name} (score: {$match['score']})");

        return null;
    }
}
