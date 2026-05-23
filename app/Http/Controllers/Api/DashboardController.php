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

        $institutionAccreditation = null;
        if (in_array($activeRole, ['Super Admin', 'LPM', 'Rektor'])) {
            $institutionAccreditation = $this->dashboardService->getInstitutionAccreditation($instrumenId);
        }

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardService->getStats($scopeParams),
            'portofolioStats' => $this->dashboardService->getPortofolioStats($periodeId, $scopeParams),
            'bkdStats' => $this->dashboardService->getBkdStats($periodeId, $scopeParams),
            'recentPendidikan' => $this->dashboardService->getRecentPendidikan($periodeId, $scopeParams),
            'recentPenelitian' => $this->dashboardService->getRecentPenelitian($periodeId, $scopeParams),
            'periode_list' => $filterData['periode_list'],
            'selectedPeriode' => $filterData['selectedPeriode'],
            'lembaga_list' => $filterData['lembaga_list'],
            'selectedInstrumenId' => (int) $instrumenId,
            'filters' => [
                'periode_id' => $periodeId,
                'instrumen_id' => $instrumenId,
            ],
            'dashboardDefaultTab' => $defaultTab,
            'peringatanStats' => $this->dashboardService->getPeringatanStats($scopeParams),
            'latestPrediction' => $this->dashboardService->getLatestPrediction($scopeParams),
            'kriteriaStats' => $this->dashboardService->getKriteriaStats($instrumenId, $periodeId, $scopeParams),
            'prodiAccreditation' => $this->dashboardService->getProdiAccreditation($filterData['activeProdis'], $periodeId),
            'institutionAccreditation' => $institutionAccreditation,
            'activeRole' => $activeRole,
            'scopeName' => $this->getScopeName($user, $activeRole),
        ]);
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
