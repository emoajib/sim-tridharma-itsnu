<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Edps;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\StandarMutu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EdpsController extends Controller
{
    /**
     * Display a paginated, filterable list of EDPS entries.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $edps = Edps::with(['prodi', 'periode', 'standarMutu'])
            ->when($request->prodi_id, function ($q, $s) {
                $q->where('prodi_id', $s);
            })
            ->when($request->periode_id, function ($q, $s) {
                $q->where('periode_id', $s);
            })
            ->when($request->standar_mutu_id, function ($q, $s) {
                $q->where('standar_mutu_id', $s);
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $edps,
            ]);
        }

        return Inertia::render('Spmi/Edps/Index', [
            'edps' => $edps,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'standar_mutu_list' => StandarMutu::select('id', 'kode_standar', 'nama_standar')->get(),
            'filters' => $request->only(['prodi_id', 'periode_id', 'standar_mutu_id', 'status']),
        ]);
    }

    /**
     * Store a newly created (or update existing) EDPS entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'standar_mutu_id' => 'required|exists:m_standar_mutu,id',
            'target' => 'nullable|numeric|min:0|max:100',
            'capaian' => 'nullable|numeric|min:0|max:100',
            'analisis' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
            'status' => 'nullable|string|in:draft,completed',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Check if entry already exists for this prodi+periode+standar
            $existing = Edps::where('prodi_id', $validated['prodi_id'])
                ->where('periode_id', $validated['periode_id'])
                ->where('standar_mutu_id', $validated['standar_mutu_id'])
                ->first();

            if ($request->hasFile('bukti_file')) {
                if ($existing && $existing->bukti_file) {
                    Storage::disk('public')->delete($existing->bukti_file);
                }
                $validated['bukti_file'] = $request->file('bukti_file')->store('edps-bukti', 'public');
            }

            if ($existing) {
                $existing->update($validated);
                $edps = $existing;
                $message = 'Data EDPS berhasil diperbarui.';
            } else {
                $edps = Edps::create($validated);
                $message = 'Data EDPS berhasil ditambahkan.';
            }

            Log::info('EDPS saved', [
                'edps_id' => $edps->id,
                'prodi_id' => $validated['prodi_id'],
                'standar_mutu_id' => $validated['standar_mutu_id'],
                'action' => $existing ? 'updated' : 'created',
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', $message);
        });
    }

    /**
     * Update the specified EDPS entry.
     */
    public function update(Request $request, Edps $edps)
    {
        $validated = $request->validate([
            'target' => 'nullable|numeric|min:0|max:100',
            'capaian' => 'nullable|numeric|min:0|max:100',
            'analisis' => 'nullable|string',
            'bukti_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
            'status' => 'nullable|string|in:draft,completed',
        ]);

        DB::transaction(function () use ($request, $edps, $validated) {
            if ($request->hasFile('bukti_file')) {
                if ($edps->bukti_file) {
                    Storage::disk('public')->delete($edps->bukti_file);
                }
                $validated['bukti_file'] = $request->file('bukti_file')->store('edps-bukti', 'public');
            }

            $edps->update($validated);

            Log::info('EDPS updated', [
                'edps_id' => $edps->id,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Data EDPS berhasil diperbarui.');
    }

    public function destroy(Edps $edps)
    {
        $edps->delete();
        return redirect()->route('spmi.edps')->with('success', 'EDPS berhasil dihapus.');
    }
}
