<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\KegiatanPendidikan;
use App\Models\Penelitian;
use App\Models\Publikasi;
use App\Models\Pkm;
use App\Models\Penunjang;
use App\Models\Bkd;
use App\Models\MahasiswaBimbingan;
use App\Models\DokumenBukti;
use App\Models\PeriodeAkademik;
use App\Models\AgentPeringatanLog;
use App\Models\AgentVerifikasiHasil;
use App\Models\AgentPredictionHistory;
use App\Models\LembagaAkreditasi;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $defaultTab = Setting::get('dashboard_default_tab', 'overview');

        $redirectRoutes = [
            'portofolio' => route('portofolio'),
            'bkd' => route('bkd'),
            'ai' => route('peringatan'),
        ];

        if ($defaultTab !== 'overview' && isset($redirectRoutes[$defaultTab])) {
            return redirect($redirectRoutes[$defaultTab]);
        }

        $periodeId = $request->get('periode_id');
        $instrumenId = $request->get('instrumen_id'); // ID dari m_lembaga_akreditasi

        // Default to BAN-PT if not specified
        if (!$instrumenId) {
            $instrumenId = LembagaAkreditasi::where('singkatan', 'BAN-PT')->first()?->id;
        }

        $stats = [
            'dosen_count' => Dosen::count(),
            'prodi_count' => Prodi::count(),
            'fakultas_count' => Fakultas::count(),
        ];

        $query = fn($q) => $periodeId ? $q->where('periode_id', $periodeId) : $q;

        // Dynamic Program Discovery based on selected Instrument
        $activeProdis = Prodi::where('lembaga_akreditasi_id', $instrumenId)
            ->with('fakultas')
            ->get();

        $portofolioStats = [
            'pendidikan_count' => $query(KegiatanPendidikan::query())->count(),
            'penelitian_count' => $query(Penelitian::query())->count(),
            'publikasi_count' => $query(Publikasi::query())->count(),
            'pkm_count' => $query(Pkm::query())->count(),
            'penunjang_count' => $query(Penunjang::query())->count(),
            'bkd_count' => $query(Bkd::query())->count(),
            'bimbingan_count' => $query(MahasiswaBimbingan::query())->count(),
            'dokumen_count' => DokumenBukti::count(),
        ];

        $recentPendidikan = $query(KegiatanPendidikan::with('dosen'))->latest()->take(5)->get();
        $recentPenelitian = $query(Penelitian::with('dosen'))->latest()->take(5)->get();

        $bkdStats = [
            'total' => $query(Bkd::query())->count(),
            'disetujui' => (clone $query(Bkd::query()))->where('status', 'disetujui')->count(),
            'draft' => (clone $query(Bkd::query()))->where('status', 'draft')->count(),
            'diajukan' => (clone $query(Bkd::query()))->where('status', 'diajukan')->count(),
            'rata_sks' => (clone $query(Bkd::query()))->avg('total_sks') ?? 0,
        ];

        // AI Agent Stats
        $peringatanStats = [
            'critical' => AgentPeringatanLog::where('tingkat', 'critical')->count(),
            'warning' => AgentPeringatanLog::where('tingkat', 'warning')->count(),
            'info' => AgentPeringatanLog::where('tingkat', 'info')->count(),
            'unread' => AgentPeringatanLog::where('is_read', false)->count(),
            'total' => AgentPeringatanLog::count(),
        ];

        // Multi-Prodi Accreditation Info (FILTERED BY INSTRUMENT)
        $prodiAccreditation = $activeProdis->map(function($p) use ($periodeId) {
            $latestSim = AgentPredictionHistory::where('prodi_id', $p->id)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->latest()
                ->first();
            
            return [
                'id' => $p->id,
                'nama' => $p->nama_prodi,
                'fakultas' => $p->fakultas->nama_fakultas ?? '-',
                'status_saat_ini' => $p->akreditasi ?? 'Belum Terakreditasi',
                'skor_simulasi' => $latestSim ? $latestSim->skor_prediksi : 0,
                'trend' => rand(-5, 10) / 100,
            ];
        });

        // Institutional info (Only if BAN-PT is selected)
        $institutionAccreditation = null;
        if (LembagaAkreditasi::find($instrumenId)?->singkatan === 'BAN-PT') {
            $institutionAccreditation = [
                'nama' => 'ITSNU Pekalongan',
                'status_saat_ini' => Setting::get('aipt_status', 'Baik'),
                'skor_simulasi' => Setting::get('aipt_sim_score', 3.12),
                'target' => 'Unggul',
                'last_sync' => now()->subDays(2)->format('d M Y'),
            ];
        }

        $latestPrediction = AgentPredictionHistory::latest()->first();
        $periode_list = PeriodeAkademik::select('id', 'nama_periode')->get();
        $selectedPeriode = $periodeId ? PeriodeAkademik::find($periodeId) : null;
        $lembaga_list = LembagaAkreditasi::where('is_active', true)->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'portofolioStats' => $portofolioStats,
            'bkdStats' => $bkdStats,
            'recentPendidikan' => $recentPendidikan,
            'recentPenelitian' => $recentPenelitian,
            'periode_list' => $periode_list,
            'selectedPeriode' => $selectedPeriode,
            'lembaga_list' => $lembaga_list,
            'selectedInstrumenId' => (int) $instrumenId,
            'filters' => [
                'periode_id' => $periodeId,
                'instrumen_id' => $instrumenId,
            ],
            'dashboardDefaultTab' => $defaultTab,
            'peringatanStats' => $peringatanStats,
            'latestPrediction' => $latestPrediction,
            'prodiAccreditation' => $prodiAccreditation,
            'institutionAccreditation' => $institutionAccreditation,
        ]);
    }
}
