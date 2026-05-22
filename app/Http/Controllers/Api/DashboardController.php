<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $defaultTab = $this->dashboardService->getDefaultTab();

        $redirectRoutes = [
            'portofolio' => route('portofolio'),
            'bkd' => route('bkd'),
            'ai' => route('peringatan'),
        ];

        if ($defaultTab !== 'overview' && isset($redirectRoutes[$defaultTab])) {
            return redirect($redirectRoutes[$defaultTab]);
        }

        $periodeId = $request->get('periode_id');
        $instrumenId = $request->get('instrumen_id') ?? $this->dashboardService->getDefaultInstrumentId();

        $filterData = $this->dashboardService->getFilterData($instrumenId, $periodeId);

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'portofolioStats' => $this->dashboardService->getPortofolioStats($periodeId),
            'bkdStats' => $this->dashboardService->getBkdStats($periodeId),
            'recentPendidikan' => $this->dashboardService->getRecentPendidikan($periodeId),
            'recentPenelitian' => $this->dashboardService->getRecentPenelitian($periodeId),
            'periode_list' => $filterData['periode_list'],
            'selectedPeriode' => $filterData['selectedPeriode'],
            'lembaga_list' => $filterData['lembaga_list'],
            'selectedInstrumenId' => (int) $instrumenId,
            'filters' => [
                'periode_id' => $periodeId,
                'instrumen_id' => $instrumenId,
            ],
            'dashboardDefaultTab' => $defaultTab,
            'peringatanStats' => $this->dashboardService->getPeringatanStats(),
            'latestPrediction' => $this->dashboardService->getLatestPrediction(),
            'kriteriaStats' => $this->dashboardService->getKriteriaStats($instrumenId, $periodeId),
            'prodiAccreditation' => $this->dashboardService->getProdiAccreditation($filterData['activeProdis'], $periodeId),
            'institutionAccreditation' => $this->dashboardService->getInstitutionAccreditation($instrumenId),
        ]);
    }
}
