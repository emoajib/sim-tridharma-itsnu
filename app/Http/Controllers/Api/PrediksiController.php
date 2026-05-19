<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentPredictionHistory;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrediksiController extends Controller
{
    public function index(Request $request)
    {
        $prediksi = AgentPredictionHistory::with(['prodi', 'periode'])
            ->when($request->prodi_id, fn($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->periode_id, fn($q) => $q->where('periode_id', $request->periode_id))
            ->orderByDesc('created_at')
            ->paginate(20);

        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $periode_list = PeriodeAkademik::orderByDesc('tahun')->orderByDesc('semester')->get();

        $stats = [
            'total' => AgentPredictionHistory::count(),
            'unggul' => AgentPredictionHistory::where('probabilitas_unggul', '>=', 50)->count(),
            'baik_sekali' => AgentPredictionHistory::where('probabilitas_unggul', '<', 50)
                ->where('probabilitas_baik_sekali', '>=', 50)->count(),
            'prodi_terbaik' => AgentPredictionHistory::orderByDesc('skor_prediksi')->first(),
        ];

        return Inertia::render('Prediksi/Index', [
            'prediksi' => $prediksi,
            'prodi_list' => $prodi_list,
            'periode_list' => $periode_list,
            'stats' => $stats,
            'filters' => [
                'prodi_id' => $request->prodi_id,
                'periode_id' => $request->periode_id,
            ],
        ]);
    }

    public function runAgent(Request $request)
    {
        $request->validate([
            'prodi_id' => 'nullable|integer',
            'periode_id' => 'nullable|integer',
        ]);

        $prodiId = $request->prodi_id ?: null;
        $periodeId = $request->periode_id ?: null;
        
        \App\Jobs\AgentDispatchJob::dispatch('prediksi', 'run', [
            'prodi_id' => $prodiId,
            'periode_id' => $periodeId,
        ]);

        return back()->with('success', 'Agent Prediksi sedang dijalankan...');
    }

    public function latest(Request $request)
    {
        $prodiId = $request->prodi_id;
        
        $prediksi = AgentPredictionHistory::with(['prodi', 'periode'])
            ->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId))
            ->orderByDesc('created_at')
            ->first();

        return response()->json($prediksi);
    }
}