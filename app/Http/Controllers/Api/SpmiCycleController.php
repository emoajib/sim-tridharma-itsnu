<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\SpmiCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SpmiCycleController extends Controller
{
    /**
     * Display a paginated list of SPMI cycles.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $cycles = SpmiCycle::query()
            ->when($request->prodi_id, function ($q, $s) {
                $q->where('prodi_id', $s);
            })
            ->when($request->periode_id, function ($q, $s) {
                $q->where('periode_id', $s);
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->when($request->tahap, function ($q, $s) {
                $q->where('tahap', $s);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $cycles,
            ]);
        }

        return Inertia::render('Spmi/Cycle/Index', [
            'cycles' => $cycles,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'filters' => $request->only(['prodi_id', 'periode_id', 'status', 'tahap']),
        ]);
    }

    /**
     * Store a newly created SPMI cycle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'instrumen_id' => 'nullable|exists:m_instrumen_akreditasi,id',
            'tahap' => 'required|string|in:penetapan,pelaksanaan,evaluasi,pengendalian,peningkatan',
            'kategori' => 'required|string|max:100',
            'nama_siklus' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'persentase_selesai' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string|in:planned,in_progress,completed,cancelled',
            'catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            SpmiCycle::create($validated);
        });

        Log::info('SPMI cycle created', [
            'tahap' => $validated['tahap'],
            'nama_siklus' => $validated['nama_siklus'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Siklus SPMI berhasil ditambahkan.');
    }

    /**
     * Update the specified SPMI cycle.
     */
    public function update(Request $request, SpmiCycle $spmiCycle)
    {
        $validated = $request->validate([
            'tahap' => 'sometimes|string|in:penetapan,pelaksanaan,evaluasi,pengendalian,peningkatan',
            'kategori' => 'nullable|string|max:100',
            'nama_siklus' => 'sometimes|string|max:255',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'persentase_selesai' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string|in:planned,in_progress,completed,cancelled',
            'catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($spmiCycle, $validated) {
            $spmiCycle->update($validated);
        });

        Log::info('SPMI cycle updated', [
            'id' => $spmiCycle->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Siklus SPMI berhasil diperbarui.');
    }

    public function destroy(SpmiCycle $spmiCycle)
    {
        $spmiCycle->delete();
        return redirect()->route('spmi.cycle')->with('success', 'Siklus berhasil dihapus.');
    }
}
