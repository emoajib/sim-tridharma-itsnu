<?php

namespace App\Services\SPMI;

use App\Models\AuditMutu;
use App\Models\Capa;
use App\Models\Edps;
use App\Models\StandarMutu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpmiDashboardService
{
    const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private AuditAnalysisService $analysisService,
        private CapaService $capaService,
    ) {}

    /**
     * Get overview metrics for the SPMI dashboard.
     */
    public function getOverview(?int $prodiId, ?int $periodeId): array
    {
        $cacheKey = "spmi_dashboard_overview_{$prodiId}_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId, $periodeId) {
            return DB::transaction(function () use ($prodiId, $periodeId) {
                $query = AuditMutu::query();
                if ($prodiId) {
                    $query->where('prodi_id', $prodiId);
                }
                if ($periodeId) {
                    $query->where('periode_id', $periodeId);
                }

                $readQuery = clone $query;

                // Aggregate temuan counts
                $aggregates = (clone $readQuery)
                    ->selectRaw("
                        COUNT(*) as total_temuan,
                        SUM(CASE WHEN status IN ('draft','submitted','assigned','in_progress','awaiting_verification','rejected') THEN 1 ELSE 0 END) as open_temuan,
                        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_temuan,
                        SUM(CASE WHEN status IN ('closed','archived') THEN 1 ELSE 0 END) as closed_temuan
                    ")
                    ->first();

                $total = (int) ($aggregates->total_temuan ?? 0);
                $closed = (int) ($aggregates->closed_temuan ?? 0);
                $closeRate = $total > 0 ? round(($closed / $total) * 100, 1) : 0;

                // Quality score
                $skorMutu = 0;
                if ($prodiId && $periodeId) {
                    try {
                        $scoreData = $this->analysisService->getProdiScore($prodiId, $periodeId);
                        $skorMutu = $scoreData['score'] ?? 0;
                    } catch (\Exception $e) {
                        Log::warning("Failed to get prodi score for dashboard", [
                            'prodi_id' => $prodiId,
                            'periode_id' => $periodeId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // CAPA overdue and approaching
                $capaOverdue = 0;
                $capaApproaching = 0;
                try {
                    $capaOverdue = $this->capaService->getOverdue()->count();
                    $capaApproaching = $this->capaService->getApproachingDeadline()->count();
                } catch (\Exception $e) {
                    Log::warning("Failed to get CAPA stats for dashboard", [
                        'error' => $e->getMessage(),
                    ]);
                }

                return [
                    'total_temuan' => $total,
                    'open_temuan' => (int) ($aggregates->open_temuan ?? 0),
                    'in_progress_temuan' => (int) ($aggregates->in_progress_temuan ?? 0),
                    'closed_temuan' => $closed,
                    'close_rate' => $closeRate,
                    'skor_mutu' => $skorMutu,
                    'capa_overdue_count' => $capaOverdue,
                    'capa_approaching_count' => $capaApproaching,
                ];
            });
        });
    }

    /**
     * Get chart-ready data for the dashboard.
     */
    public function getChartData(?int $prodiId, ?int $periodeId): array
    {
        $cacheKey = "spmi_dashboard_charts_{$prodiId}_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId, $periodeId) {
            return DB::transaction(function () use ($prodiId, $periodeId) {
                $query = AuditMutu::query();
                if ($prodiId) {
                    $query->where('prodi_id', $prodiId);
                }
                if ($periodeId) {
                    $query->where('periode_id', $periodeId);
                }

                $readQuery = clone $query;

                // 1. Temuan per standar (group by standar_mutu_id)
                $temuanPerStandar = (clone $readQuery)
                    ->selectRaw('standar_mutu_id, COUNT(*) as count')
                    ->whereNotNull('standar_mutu_id')
                    ->groupBy('standar_mutu_id')
                    ->pluck('count', 'standar_mutu_id');

                $standarIds = $temuanPerStandar->keys()->toArray();
                $standars = StandarMutu::whereIn('id', $standarIds)
                    ->get(['id', 'kode_standar', 'nama_standar'])
                    ->keyBy('id');

                $temuanPerStandarFormatted = [];
                foreach ($temuanPerStandar as $standarId => $count) {
                    $s = $standars->get($standarId);
                    $temuanPerStandarFormatted[] = [
                        'standar_id' => $standarId,
                        'kode_standar' => $s->kode_standar ?? 'Unknown',
                        'nama_standar' => $s->nama_standar ?? 'Unknown',
                        'count' => $count,
                    ];
                }

                // 2. Temuan per bulan (last 12 months)
                $twelveMonthsAgo = now()->subMonths(12)->startOfMonth();
                $driver = DB::connection()->getDriverName();
                $dateFormat = $driver === 'pgsql'
                    ? "TO_CHAR(created_at, 'YYYY-MM')"
                    : "DATE_FORMAT(created_at, '%Y-%m')";
                $temuanPerBulanQuery = (clone $readQuery)
                    ->where('created_at', '>=', $twelveMonthsAgo)
                    ->selectRaw("{$dateFormat} as bulan, COUNT(*) as count")
                    ->groupByRaw("{$dateFormat}")
                    ->orderBy('bulan')
                    ->get()
                    ->keyBy('bulan');

                // Fill all 12 months
                $temuanPerBulan = [];
                for ($i = 11; $i >= 0; $i--) {
                    $bulan = now()->subMonths($i)->format('Y-m');
                    $temuanPerBulan[] = [
                        'bulan' => $bulan,
                        'count' => (int) ($temuanPerBulanQuery[$bulan]->count ?? 0),
                    ];
                }

                // 3. Severity distribution
                $severityDistribution = (clone $readQuery)
                    ->selectRaw("severity, COUNT(*) as count")
                    ->groupBy('severity')
                    ->pluck('count', 'severity')
                    ->toArray();

                $severityDistribution = [
                    'ringan' => (int) ($severityDistribution['ringan'] ?? 0),
                    'sedang' => (int) ($severityDistribution['sedang'] ?? 0),
                    'berat' => (int) ($severityDistribution['berat'] ?? 0),
                    'kritis' => (int) ($severityDistribution['kritis'] ?? 0),
                ];

                return [
                    'temuan_per_standar' => $temuanPerStandarFormatted,
                    'temuan_per_bulan' => $temuanPerBulan,
                    'severity_distribution' => $severityDistribution,
                ];
            });
        });
    }

    /**
     * Get PPEPP (Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan) cycle progress.
     */
    public function getPpeppProgress(?int $prodiId): array
    {
        $cacheKey = "spmi_dashboard_ppepp_{$prodiId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($prodiId) {
            // PPEPP stages mapped to audit statuses
            $ppeppStages = [
                'penetapan' => [
                    'label' => 'Penetapan Standar',
                    'statuses' => ['draft', 'submitted'],
                    'icon' => 'file-text',
                    'color' => '#6366f1',
                ],
                'pelaksanaan' => [
                    'label' => 'Pelaksanaan Standar',
                    'statuses' => ['assigned', 'in_progress'],
                    'icon' => 'play-circle',
                    'color' => '#22c55e',
                ],
                'evaluasi' => [
                    'label' => 'Evaluasi',
                    'statuses' => ['awaiting_verification', 'verified'],
                    'icon' => 'search',
                    'color' => '#f59e0b',
                ],
                'pengendalian' => [
                    'label' => 'Pengendalian',
                    'statuses' => ['rejected'],
                    'icon' => 'shield',
                    'color' => '#ef4444',
                ],
                'peningkatan' => [
                    'label' => 'Peningkatan',
                    'statuses' => ['closed', 'archived'],
                    'icon' => 'trending-up',
                    'color' => '#3b82f6',
                ],
            ];

            $query = AuditMutu::query();
            if ($prodiId) {
                $query->where('prodi_id', $prodiId);
            }

            $totalAudits = (clone $query)->count();

            $stages = [];
            foreach ($ppeppStages as $key => $stage) {
                $count = (clone $query)
                    ->whereIn('status', $stage['statuses'])
                    ->count();

                $stages[] = [
                    'key' => $key,
                    'label' => $stage['label'],
                    'count' => $count,
                    'percentage' => $totalAudits > 0 ? round(($count / $totalAudits) * 100, 1) : 0,
                    'icon' => $stage['icon'],
                    'color' => $stage['color'],
                ];
            }

            return [
                'stages' => $stages,
                'total_audits' => $totalAudits,
            ];
        });
    }
}
