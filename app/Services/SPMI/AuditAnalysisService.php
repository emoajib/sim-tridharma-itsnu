<?php

namespace App\Services\SPMI;

use App\Models\AuditMutu;
use App\Models\Capa;
use App\Models\Prodi;
use App\Models\StandarMutu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditAnalysisService
{
    const CACHE_TTL = 300; // 5 minutes

    const SEVERITY_WEIGHTS = [
        'ringan' => 2,
        'sedang' => 5,
        'berat' => 15,
        'kritis' => 30,
    ];

    const RISK_SCORES = [
        'kritis' => 20,
        'berat' => 15,
        'sedang' => 10,
        'ringan' => 5,
    ];

    /**
     * Calculate the quality score for a study program in a given period.
     * score = max(100 - sum(severity_weight × count / total_temuan × 10), 0)
     */
    public function getProdiScore(int $prodiId, int $periodeId): array
    {
        $cacheKey = "audit_analysis_score_{$prodiId}_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId, $periodeId) {
            return DB::transaction(function () use ($prodiId, $periodeId) {
                $temuan = AuditMutu::where('prodi_id', $prodiId)
                    ->where('periode_id', $periodeId)
                    ->selectRaw('severity, COUNT(*) as count')
                    ->groupBy('severity')
                    ->pluck('count', 'severity');

                $totalTemuan = $temuan->sum();

                if ($totalTemuan === 0) {
                    return [
                        'score' => 100,
                        'total_temuan' => 0,
                        'severity_breakdown' => [],
                        'deduction_detail' => [],
                    ];
                }

                $deductionDetail = [];
                $totalDeduction = 0;

                foreach (self::SEVERITY_WEIGHTS as $severity => $weight) {
                    $count = (int) ($temuan[$severity] ?? 0);
                    if ($count > 0) {
                        $deduction = ($weight * $count / $totalTemuan) * 10;
                        $totalDeduction += $deduction;
                        $deductionDetail[$severity] = [
                            'count' => $count,
                            'weight' => $weight,
                            'deduction' => round($deduction, 2),
                        ];
                    }
                }

                $score = max(0, 100 - round($totalDeduction, 2));

                return [
                    'score' => $score,
                    'total_temuan' => $totalTemuan,
                    'severity_breakdown' => $temuan->toArray(),
                    'deduction_detail' => $deductionDetail,
                    'total_deduction' => round($totalDeduction, 2),
                ];
            });
        });
    }

    /**
     * Get trend data: temuan per month, close rate, avg resolution time for last 12 months.
     */
    public function getTrend(int $prodiId, int $tahun): array
    {
        $cacheKey = "audit_analysis_trend_{$prodiId}_{$tahun}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId, $tahun) {
            $startDate = "{$tahun}-01-01";
            $endDate = "{$tahun}-12-31";

            // Temuan per month
            $temuanPerMonth = AuditMutu::where('prodi_id', $prodiId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("EXTRACT(MONTH FROM created_at) as bulan, COUNT(*) as count")
                ->groupByRaw("EXTRACT(MONTH FROM created_at)")
                ->orderBy('bulan')
                ->pluck('count', 'bulan')
                ->toArray();

            // Close rate per month
            $closeRatePerMonth = AuditMutu::where('prodi_id', $prodiId)
                ->whereBetween('closed_at', [$startDate, $endDate])
                ->where('status', 'closed')
                ->selectRaw("EXTRACT(MONTH FROM closed_at) as bulan, COUNT(*) as count")
                ->groupByRaw("EXTRACT(MONTH FROM closed_at)")
                ->orderBy('bulan')
                ->pluck('count', 'bulan')
                ->toArray();

            // Average resolution time (days from created to closed)
            $avgResolution = AuditMutu::where('prodi_id', $prodiId)
                ->whereNotNull('closed_at')
                ->whereBetween('closed_at', [$startDate, $endDate])
                ->selectRaw("AVG(DATEDIFF(closed_at, created_at)) as avg_days")
                ->value('avg_days');

            // Build monthly series (1-12)
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[] = [
                    'bulan' => $m,
                    'temuan' => (int) ($temuanPerMonth[$m] ?? 0),
                    'closed' => (int) ($closeRatePerMonth[$m] ?? 0),
                    'close_rate' => $this->calculatePercentage(
                        $closeRatePerMonth[$m] ?? 0,
                        $temuanPerMonth[$m] ?? 0
                    ),
                ];
            }

            return [
                'tahun' => $tahun,
                'months' => $months,
                'avg_resolution_days' => round((float) ($avgResolution ?? 0), 1),
                'total_temuan' => array_sum($temuanPerMonth),
                'total_closed' => array_sum($closeRatePerMonth),
            ];
        });
    }

    /**
     * Generate risk heatmap matrix: severity × standar_mutu category.
     */
    public function getRiskHeatmap(int $prodiId, int $periodeId): array
    {
        $cacheKey = "audit_analysis_heatmap_{$prodiId}_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId, $periodeId) {
            $severities = ['ringan', 'sedang', 'berat', 'kritis'];

            $categories = StandarMutu::select('kategori')
                ->distinct()
                ->pluck('kategori')
                ->toArray();

            $matrix = AuditMutu::where('prodi_id', $prodiId)
                ->where('periode_id', $periodeId)
                ->join('m_standar_mutu', 'trx_audit_mutu.standar_mutu_id', '=', 'm_standar_mutu.id')
                ->selectRaw("m_standar_mutu.kategori, trx_audit_mutu.severity, COUNT(*) as count")
                ->groupByRaw("m_standar_mutu.kategori, trx_audit_mutu.severity")
                ->get()
                ->groupBy('kategori')
                ->map(fn ($items) => $items->pluck('count', 'severity'))
                ->toArray();

            $rows = [];
            foreach ($categories as $category) {
                $row = ['kategori' => $category];
                foreach ($severities as $sev) {
                    $row[$sev] = (int) ($matrix[$category][$sev] ?? 0);
                }
                $rows[] = $row;
            }

            return [
                'severities' => $severities,
                'categories' => $categories,
                'matrix' => $rows,
            ];
        });
    }

    /**
     * Get early warning data for a specific prodi or all prodi.
     */
    public function getEarlyWarning(?int $prodiId = null): array
    {
        $cacheKey = 'audit_analysis_early_warning_'.($prodiId ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId) {
            $query = AuditMutu::query();
            if ($prodiId !== null) {
                $query->where('prodi_id', $prodiId);
            }

            // 1. Kritis findings threshold
            $kritisFindings = (clone $query)
                ->where('severity', 'kritis')
                ->whereNotIn('status', ['closed', 'archived'])
                ->with('prodi:id,nama_prodi')
                ->get()
                ->groupBy('prodi_id')
                ->map(fn ($items, $pid) => [
                    'prodi_id' => $pid,
                    'prodi_nama' => $items->first()->prodi?->nama_prodi ?? 'Unknown',
                    'count' => $items->count(),
                    'items' => $items->take(5)->pluck('judul_audit'),
                ])
                ->values()
                ->toArray();

            // 2. Temuan tanpa CAPA > 14 days (open temuan without CAPA for > 14 days)
            $temuanTanpaCapa = (clone $query)
                ->whereNotIn('status', ['closed', 'archived', 'draft'])
                ->whereDoesntHave('capas')
                ->where('created_at', '<', now()->subDays(14))
                ->with('prodi:id,nama_prodi')
                ->get()
                ->groupBy('prodi_id')
                ->map(fn ($items, $pid) => [
                    'prodi_id' => $pid,
                    'prodi_nama' => $items->first()->prodi?->nama_prodi ?? 'Unknown',
                    'count' => $items->count(),
                    'hari_tanpa_capa' => $items->min(fn ($i) => $i->created_at->diffInDays()),
                    'items' => $items->take(5)->pluck('judul_audit'),
                ])
                ->values()
                ->toArray();

            // 3. Deadline approaching: temuan with deadline < 7 days
            $deadlineApproaching = (clone $query)
                ->whereNotNull('deadline_tindak_lanjut')
                ->where('deadline_tindak_lanjut', '<', now()->addDays(7))
                ->where('deadline_tindak_lanjut', '>=', now())
                ->whereNotIn('status', ['closed', 'archived', 'verified'])
                ->with('prodi:id,nama_prodi')
                ->get()
                ->groupBy('prodi_id')
                ->map(fn ($items, $pid) => [
                    'prodi_id' => $pid,
                    'prodi_nama' => $items->first()->prodi?->nama_prodi ?? 'Unknown',
                    'count' => $items->count(),
                    'nearest_deadline' => $items->min('deadline_tindak_lanjut')?->format('Y-m-d'),
                    'items' => $items->take(5)->pluck('judul_audit'),
                ])
                ->values()
                ->toArray();

            return [
                'kritis_findings' => $kritisFindings,
                'temuan_tanpa_capa' => $temuanTanpaCapa,
                'deadline_approaching' => $deadlineApproaching,
            ];
        });
    }

    /**
     * Get ranking of all study programs by quality score for a given period.
     */
    public function getProdiRanking(int $periodeId): Collection
    {
        $cacheKey = "audit_analysis_ranking_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($periodeId) {
            $prodis = Prodi::where('is_active', true)->get(['id', 'nama_prodi', 'fakultas_id']);

            $rankings = collect();

            foreach ($prodis as $prodi) {
                $scoreData = $this->getProdiScore($prodi->id, $periodeId);
                $rankings->push([
                    'prodi_id' => $prodi->id,
                    'nama_prodi' => $prodi->nama_prodi,
                    'fakultas_id' => $prodi->fakultas_id,
                    'score' => $scoreData['score'],
                    'total_temuan' => $scoreData['total_temuan'],
                ]);
            }

            return $rankings->sortByDesc('score')->values();
        });
    }

    /**
     * Calculate percentage, avoiding division by zero.
     */
    private function calculatePercentage(int $part, int $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0.0;
    }
}
