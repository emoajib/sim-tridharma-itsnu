<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\KegiatanPendidikan;
use App\Models\Penelitian;
use App\Models\Penunjang;
use App\Models\PeriodeAkademik;
use App\Models\Pkm;
use App\Models\Prodi;
use App\Models\Publikasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortofolioController extends Controller
{
    public function index(Request $request)
    {
        $dosenId = $request->dosen_id;
        $periodeId = $request->periode_id;

        $query = fn ($q) => $q;

        $pendidikanQuery = KegiatanPendidikan::query();
        $penelitianQuery = Penelitian::query();
        $publikasiQuery = Publikasi::query();
        $pkmQuery = Pkm::query();
        $penunjangQuery = Penunjang::query();

        if ($dosenId) {
            $pendidikanQuery->where('dosen_id', $dosenId);
            $penelitianQuery->where('dosen_id', $dosenId);
            $publikasiQuery->where('dosen_id', $dosenId);
            $pkmQuery->where('dosen_id', $dosenId);
            $penunjangQuery->where('dosen_id', $dosenId);
        }

        if ($periodeId) {
            $pendidikanQuery->where('periode_id', $periodeId);
            $penelitianQuery->where('periode_id', $periodeId);
            $publikasiQuery->where('periode_id', $periodeId);
            $pkmQuery->where('periode_id', $periodeId);
            $penunjangQuery->where('periode_id', $periodeId);
        }

        return Inertia::render('Portofolio/Index', [
            'pendidikan_count' => $pendidikanQuery->count(),
            'penelitian_count' => $penelitianQuery->count(),
            'publikasi_count' => $publikasiQuery->count(),
            'pkm_count' => $pkmQuery->count(),
            'penunjang_count' => $penunjangQuery->count(),
            'recent_pendidikan' => (clone $pendidikanQuery)->with('dosen')->latest()->take(5)->get(),
            'recent_penelitian' => (clone $penelitianQuery)->with('dosen')->latest()->take(5)->get(),
            'recent_publikasi' => (clone $publikasiQuery)->with('dosen')->latest()->take(5)->get(),
            'recent_pkm' => (clone $pkmQuery)->with('dosen')->latest()->take(5)->get(),
            'recent_penunjang' => (clone $penunjangQuery)->with('dosen')->latest()->take(5)->get(),
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nama_belakang')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }
}
