<?php

namespace App\Services;

use App\Models\AgentPeringatanLog;
use App\Models\AgentPredictionHistory;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;

class DashboardExportService
{
    public function exportToPdf(Request $request): array
    {
        $periodeId = $request->get('periode_id');
        $instrumenId = $request->get('instrumen_id');

        $stats = [
            'dosen_count' => Dosen::count(),
            'prodi_count' => Prodi::count(),
            'fakultas_count' => Fakultas::count(),
        ];

        $prodis = Prodi::with(['fakultas'])
            ->when($instrumenId, fn ($q) => $q->where('lembaga_akreditasi_id', $instrumenId))
            ->get()
            ->map(function ($p) use ($periodeId) {
                $latestPrediction = AgentPredictionHistory::where('prodi_id', $p->id)
                    ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
                    ->latest()
                    ->first();

                return [
                    'nama_prodi' => $p->nama_prodi,
                    'fakultas' => $p->fakultas->nama_fakultas ?? '-',
                    'skor_prediksi' => $latestPrediction ? $latestPrediction->skor_prediksi : '-',
                    'predikat' => $this->getPredikat($latestPrediction),
                ];
            });

        $latestPrediction = AgentPredictionHistory::with('prodi')
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->latest()
            ->first();

        $peringatanStats = [
            'critical' => AgentPeringatanLog::where('tingkat', 'critical')->count(),
            'warning' => AgentPeringatanLog::where('tingkat', 'warning')->count(),
            'info' => AgentPeringatanLog::where('tingkat', 'info')->count(),
        ];

        return [
            'stats' => $stats,
            'prodis' => $prodis,
            'latestPrediction' => $latestPrediction,
            'peringatanStats' => $peringatanStats,
            'periode' => $periodeId ? PeriodeAkademik::find($periodeId)?->nama_periode : 'Semua Periode',
            'generated_at' => now()->format('d F Y H:i'),
        ];
    }

    private function getPredikat($prediction): string
    {
        if (! $prediction) {
            return '-';
        }

        $probUnggul = $prediction->probabilitas_unggul ?? 0;

        if ($probUnggul >= 50) {
            return 'UNGGUL';
        }
        if ($probUnggul >= 30) {
            return 'BAIK SEKALI';
        }

        return 'BAIK';
    }
}
