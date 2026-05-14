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
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->get('periode_id');

        $stats = [
            'dosen_count' => Dosen::count(),
            'prodi_count' => Prodi::count(),
            'fakultas_count' => Fakultas::count(),
        ];

        $query = fn($q) => $periodeId ? $q->where('periode_id', $periodeId) : $q;

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
        $recentPublikasi = $query(Publikasi::with('dosen'))->latest()->take(5)->get();
        $recentPkm = $query(Pkm::with('dosen'))->latest()->take(5)->get();

        $bkdStats = [
            'total' => $query(Bkd::query())->count(),
            'disetujui' => (clone $query(Bkd::query()))->where('status', 'disetujui')->count(),
            'draft' => (clone $query(Bkd::query()))->where('status', 'draft')->count(),
            'diajukan' => (clone $query(Bkd::query()))->where('status', 'diajukan')->count(),
            'rata_sks' => (clone $query(Bkd::query()))->avg('total_sks') ?? 0,
        ];

        $periode_list = PeriodeAkademik::select('id', 'nama_periode')->get();
        $selectedPeriode = $periodeId ? PeriodeAkademik::find($periodeId) : null;

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'portofolioStats' => $portofolioStats,
            'bkdStats' => $bkdStats,
            'recentPendidikan' => $recentPendidikan,
            'recentPenelitian' => $recentPenelitian,
            'recentPublikasi' => $recentPublikasi,
            'recentPkm' => $recentPkm,
            'periode_list' => $periode_list,
            'selectedPeriode' => $selectedPeriode,
        ]);
    }
}
