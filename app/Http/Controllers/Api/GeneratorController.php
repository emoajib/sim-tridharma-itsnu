<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use App\Models\AgentGeneratorHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneratorController extends Controller
{
    public function index(Request $request)
    {
        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $periode_list = PeriodeAkademik::select('id', 'nama_periode')->orderByDesc('tanggal_mulai')->get();
        
        $history = AgentGeneratorHistory::with(['prodi', 'periode'])
            ->when($request->prodi_id, fn($q) => $q->where('prodi_id', $request->prodi_id))
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Generator/Index', [
            'prodi_list' => $prodi_list,
            'periode_list' => $periode_list,
            'history' => $history,
            'filters' => [
                'prodi_id' => $request->prodi_id,
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|integer',
            'periode_id' => 'required|integer',
            'jenis_dokumen' => 'required|in:led,lkpt',
        ]);

        \App\Jobs\AgentDispatchJob::dispatch('generator', 'run', [
            'prodi_id' => $request->prodi_id,
            'periode_id' => $request->periode_id,
            'jenis_dokumen' => $request->jenis_dokumen,
        ]);

        return back()->with('success', 'Generator dokumen sedang dijalankan. Hasil akan muncul di history.');
    }
}