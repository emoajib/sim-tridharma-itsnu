<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CascadingIku;
use App\Models\IndikatorIku;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Http\Request;

class CascadingIkuController extends Controller
{
    public function index(Request $request)
    {
        $cascading = CascadingIku::with(['iku', 'periode'])
            ->when($request->periode_id, fn($q, $v) => $q->where('periode_id', $v))
            ->when($request->unit_type, fn($q, $v) => $q->where('unit_type', $v))
            ->when($request->unit_id, fn($q, $v) => $q->where('unit_id', $v))
            ->paginate(10);

        // Manually load unit relation because it's dynamic
        $cascading->getCollection()->each(function ($item) {
            $item->load('unit');
        });

        if (request()->wantsJson()) {
            return response()->json($cascading);
        }

        return inertia('Iku/Cascading', [
            'cascading' => $cascading,
            'iku_list' => IndikatorIku::where('is_active', true)->get(['id', 'nama_indikator', 'kode_iku']),
            'periode_list' => PeriodeAkademik::orderBy('kode_periode', 'desc')->get(['id', 'kode_periode', 'nama_periode']),
            'prodi_list' => Prodi::all(['id', 'nama_prodi']),
            'fakultas_list' => Fakultas::all(['id', 'nama_fakultas']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iku_id' => 'required|exists:m_indikator_iku,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'unit_type' => 'required|in:Fakultas,Prodi',
            'unit_id' => 'required|integer',
            'target' => 'required|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $cascading = CascadingIku::create($validated);

        return redirect()->route('iku.cascading')->with('success', 'Cascading IKU berhasil dibuat.');
    }

    public function updateCapaian(Request $request, CascadingIku $cascading)
    {
        $validated = $request->validate([
            'capaian' => 'required|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $cascading->update($validated);

        return redirect()->back()->with('success', 'Capaian cascading IKU berhasil diperbarui.');
    }
}
