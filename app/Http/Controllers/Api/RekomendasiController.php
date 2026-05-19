<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentRekomendasiLog;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    public function index(Request $request)
    {
        $prodis = Prodi::with('fakultas')->get();
        $periodes = PeriodeAkademik::orderBy('tanggal_mulai', 'desc')->get();

        $rekomendasis = AgentRekomendasiLog::with(['prodi', 'indikator'])
            ->when($request->prodi_id, fn($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return inertia('Agent/Rekomendasi/Index', [
            'rekomendasis' => $rekomendasis,
            'prodis' => $prodis,
            'periodes' => $periodes,
        ]);
    }

    public function run(Request $request)
    {
        \App\Jobs\AgentDispatchJob::dispatch('rekomendasi', 'run', $request->all());

        return back()->with('success', 'Agent Rekomendasi sedang diproses.');
    }
}