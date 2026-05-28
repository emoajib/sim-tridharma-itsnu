<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use App\Services\SPMI\SpmiReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpmiReportController extends Controller
{
    public function __construct(
        private SpmiReportService \$reportService,
    ) {}

    /**
     * Display the SPMI report index/overview.
     */
    public function index(Request \$request): Response|JsonResponse
    {
        \$periodeId = \$request->integer('periode_id', null) ?: null;
        
        // If no period selected, use current active period
        if (!\$periodeId) {
            \$activePeriode = PeriodeAkademik::where('is_active', true)->first();
            \$periodeId = \$activePeriode?->id;
        }

        \$reportData = \$periodeId ? \$this->reportService->generateLaporanSpmi(\$periodeId) : null;

        if (\$request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => \$reportData,
            ]);
        }

        return Inertia::render('Spmi/Report/Index', [
            'report_data' => \$reportData,
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'filters' => \$request->only(['periode_id']),
        ]);
    }

    /**
     * Get PDDIKTI specific reporting data.
     */
    public function pddikti(Request \$request): JsonResponse
    {
        \$periodeId = \$request->integer('periode_id', null);
        
        if (!\$periodeId) {
            return response()->json(['message' => 'Periode ID is required'], 400);
        }

        \$data = \$this->reportService->getPelaporanPddikti(\$periodeId);

        return response()->json([
            'success' => true,
            'data' => \$data,
        ]);
    }
}
