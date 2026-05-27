<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\SPMI\SpmiDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private SpmiDashboardService $spmiDashboardService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $activeRole = $user->activeRole();

        // Calculate scope parameters
        $scopeParams = [];
        $warnings = [];

        if ($activeRole === 'Dosen') {
            if ($user->dosen_id) {
                $scopeParams['dosen_id'] = $user->dosen_id;
            } else {
                $warnings[] = 'Profil Dosen belum tertaut. Beberapa data mungkin tidak muncul.';
            }
        } elseif (in_array($activeRole, ['Kaprodi', 'Staf Prodi'])) {
            if ($user->prodi_id) {
                $scopeParams['prodi_id'] = $user->prodi_id;
            } else {
                $warnings[] = 'Data Program Studi belum tertaut. Dashboard menampilkan data terbatas.';
            }
        } elseif ($activeRole === 'Dekan') {
            $user->loadMissing('prodi');
            if ($user->prodi && $user->prodi->fakultas_id) {
                $scopeParams['fakultas_id'] = $user->prodi->fakultas_id;
            } else {
                $warnings[] = 'Data Fakultas (via Prodi) belum tertaut. Dashboard menampilkan data terbatas.';
            }
        }

        if (! empty($warnings) && $activeRole !== 'Super Admin') {
            session()->now('warning', implode(' ', $warnings));
        }

        $defaultTab = $this->dashboardService->getDefaultTab();

        $redirectRoutes = [
            'portofolio' => route('portofolio'),
            'bkd' => route('bkd'),
            'ai' => route('peringatan'),
        ];

        if ($defaultTab !== 'overview') {
            if (isset($redirectRoutes[$defaultTab])) {
                return redirect($redirectRoutes[$defaultTab]);
            }
            $defaultTab = 'overview';
        }

        $periodeId = $request->get('periode_id');
        $instrumenId = $request->get('instrumen_id') ?? $this->dashboardService->getDefaultInstrumentId();

        $filterData = $this->dashboardService->getFilterData($instrumenId, $periodeId, $scopeParams);

        // ── Redis caching layer: cache heavy dashboard queries for 5 minutes ──
        $cacheKey = 'dashboard:' . $user->id . ':' . md5(serialize($scopeParams))
            . ':' . ($periodeId ?? '0') . ':' . $instrumenId;

        [
            $institutionAccreditation,
            $dashboardData,
            $spmiOverview,
            $spmiCharts,
            $spmiPpepp,
        ] = Cache::remember($cacheKey, 300, function () use (
            $scopeParams, $periodeId, $instrumenId, $activeRole, $filterData
        ) {
            $institutionAccreditation = null;
            if (in_array($activeRole, ['Super Admin', 'LPM', 'Rektor'])) {
                $institutionAccreditation = $this->dashboardService->getInstitutionAccreditation($instrumenId);
            }

            $prodiId = $scopeParams['prodi_id'] ?? null;

            $spmiOverview = [];
            $spmiCharts = [];
            $spmiPpepp = [];
            try {
                $spmiOverview = $this->spmiDashboardService->getOverview($prodiId, $periodeId);
                $spmiCharts = $this->spmiDashboardService->getChartData($prodiId, $periodeId);
                $spmiPpepp = $this->spmiDashboardService->getPpeppProgress($prodiId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to load SPMI dashboard data: ' . $e->getMessage());
            }

            $dashboardData = [
                'stats' => $this->dashboardService->getStats($scopeParams),
                'portofolioStats' => $this->dashboardService->getPortofolioStats($periodeId, $scopeParams),
                'bkdStats' => $this->dashboardService->getBkdStats($periodeId, $scopeParams),
                'recentPendidikan' => $this->dashboardService->getRecentPendidikan($periodeId, $scopeParams),
                'recentPenelitian' => $this->dashboardService->getRecentPenelitian($periodeId, $scopeParams),
                'peringatanStats' => $this->dashboardService->getPeringatanStats($scopeParams),
                'latestPrediction' => $this->dashboardService->getLatestPrediction($scopeParams),
                'kriteriaStats' => $this->dashboardService->getKriteriaStats($instrumenId, $periodeId, $scopeParams),
                'prodiAccreditation' => $this->dashboardService->getProdiAccreditation($filterData['activeProdis'], $periodeId),
            ];

            return [$institutionAccreditation, $dashboardData, $spmiOverview, $spmiCharts, $spmiPpepp];
        });

        return Inertia::render('Dashboard', array_merge($dashboardData, [
            'periode_list' => $filterData['periode_list'],
            'selectedPeriode' => $filterData['selectedPeriode'],
            'lembaga_list' => $filterData['lembaga_list'],
            'selectedInstrumenId' => (int) $instrumenId,
            'filters' => [
                'periode_id' => $periodeId,
                'instrumen_id' => $instrumenId,
            ],
            'dashboardDefaultTab' => $defaultTab,
            'institutionAccreditation' => $institutionAccreditation,
            'spmi_overview' => $spmiOverview,
            'spmi_charts' => $spmiCharts,
            'spmi_ppepp' => $spmiPpepp,
            'activeRole' => $activeRole,
            'scopeName' => $this->getScopeName($user, $activeRole),
        ]));
    }

    private function getScopeName($user, $activeRole): string
    {
        if ($activeRole === 'Dosen') {
            return $user->name;
        }
        if (in_array($activeRole, ['Kaprodi', 'Staf Prodi']) && $user->prodi) {
            return $user->prodi->nama_prodi;
        }
        if ($activeRole === 'Dekan' && $user->prodi && $user->prodi->fakultas) {
            return $user->prodi->fakultas->nama_fakultas;
        }

        return 'Institusi';
    }
}
