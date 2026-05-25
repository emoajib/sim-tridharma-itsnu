<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Services\SPMI\SpmiDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpmiDashboardController extends Controller
{
    public function __construct(
        private SpmiDashboardService $dashboardService,
    ) {}

    /**
     * Display the SPMI dashboard overview.
     */
    public function overview(Request $request): Response|JsonResponse
    {
        $prodiId = $request->integer('prodi_id', null) ?: null;
        $periodeId = $request->integer('periode_id', null) ?: null;

        $overview = $this->dashboardService->getOverview($prodiId, $periodeId);
        $chartData = $this->dashboardService->getChartData($prodiId, $periodeId);
        $ppeppProgress = $this->dashboardService->getPpeppProgress($prodiId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => $overview,
                    'charts' => $chartData,
                    'ppepp' => $ppeppProgress,
                ],
            ]);
        }

        return Inertia::render('Spmi/Dashboard', [
            'overview' => $overview,
            'charts' => $chartData,
            'ppepp' => $ppeppProgress,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'filters' => $request->only(['prodi_id', 'periode_id']),
        ]);
    }

    /**
     * Get chart data for Recharts integration (JSON).
     */
    public function chartData(Request $request): JsonResponse
    {
        $prodiId = $request->integer('prodi_id', null) ?: null;
        $periodeId = $request->integer('periode_id', null) ?: null;

        $chartData = $this->dashboardService->getChartData($prodiId, $periodeId);

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }
}
